<?php
namespace App\Models;

use App\Core\Model;
use PDOException;

class FactureModel extends Model
{
    protected $table = 'facture';

    /**
     * Le tronc commun de toutes les lectures de factures.
     *
     * Deux colonnes n'existent pas dans la table et sont calculées ici :
     *
     *   montant_paye  la somme des paiements reçus pour cette facture ;
     *   statut        « non payee » / « partiellement_payee » / « totalement_payee ».
     *
     * On ne stocke pas ces deux valeurs dans facture : elles se déduisent des
     * paiements. Les recalculer à chaque lecture évite qu'une facture affiche
     * « soldée » alors qu'un paiement vient d'être supprimé.
     *
     * Pas d'ORDER BY ici : chaque méthode ajoute le sien, et
     * allFacturesByStatut() a besoin d'envelopper cette requête.
     */
    private function selectFactures(): string
    {
        return "SELECT f.*,
                       c.numero AS commande_numero,
                       u.nom    AS client_nom,
                       u.prenom AS client_prenom,
                       COALESCE(v.total, 0) AS montant_paye,
                       -- Le cast vers l'énumération n'est pas décoratif : si un
                       -- de ces trois libellés était mal orthographié,
                       -- PostgreSQL refuserait la requête au lieu de renvoyer
                       -- un statut que les vues ne sauraient pas afficher.
                       (CASE
                           WHEN COALESCE(v.total, 0) <= 0         THEN 'non payee'
                           WHEN COALESCE(v.total, 0) >= f.montant THEN 'totalement_payee'
                           ELSE 'partiellement_payee'
                       END)::type_statut_facture AS statut
                FROM {$this->table} f
                JOIN commande c    ON c.id = f.commande_id
                JOIN utilisateur u ON u.id = c.client_id
                -- LEFT JOIN : une facture sans aucun paiement doit quand même
                -- apparaître dans la liste (avec un total à 0).
                LEFT JOIN (
                    SELECT facture_id, SUM(montant_verse) AS total
                    FROM paiement
                    GROUP BY facture_id
                ) v ON v.facture_id = f.id";
    }

    public function allFactures(): array
    {
        return $this->executeSelect(
            $this->selectFactures() . " ORDER BY f.date DESC, f.id DESC"
        );
    }

    /**
     * Variantes « impayées » et « soldées » de la liste.
     *
     * statut est un calcul, pas une colonne : PostgreSQL n'accepte pas de le
     * filtrer dans le WHERE de la requête qui le construit. D'où la requête
     * enveloppante, qui traite le résultat comme une table ordinaire.
     */
    public function allFacturesByStatut(string $statut): array
    {
        $sql = "SELECT * FROM ({$this->selectFactures()}) AS f
                WHERE f.statut = ?
                ORDER BY f.date DESC, f.id DESC";

        return $this->executeSelect($sql, [$statut]);
    }

    public function findFacture(int $id)
    {
        return $this->executeSelectOne(
            $this->selectFactures() . " WHERE f.id = ?",
            [$id]
        );
    }

    /**
     * Sert à deux choses : empêcher une deuxième facture sur la même commande
     * (facture.commande_id est UNIQUE) et afficher « Voir la facture » plutôt
     * que « Générer la facture » sur l'écran de la commande.
     */
    public function findByCommande(int $commandeId)
    {
        return $this->executeSelectOne(
            "SELECT * FROM {$this->table} WHERE commande_id = ?",
            [$commandeId]
        );
    }

    /**
     * Cas d'utilisation « générer la facture d'une commande ».
     *
     * Même contrainte que pour les commandes : numero est NOT NULL et se
     * construit à partir de l'id, que PostgreSQL n'attribue qu'à l'insertion.
     * On demande donc le prochain id à la séquence avant d'insérer.
     *
     * @return int L'id de la facture créée.
     */
    public function createFacture(int $commandeId, float $montant): int
    {
        $suivant = $this->executeSelectOne(
            "SELECT nextval(pg_get_serial_sequence('{$this->table}', 'id')) AS id"
        );

        $factureId = (int) $suivant->id;

        $this->executeUpdate(
            "INSERT INTO {$this->table} (id, numero, date, montant, commande_id)
             VALUES (:id, :numero, CURRENT_DATE, :montant, :commande_id)",
            [
                'id'          => $factureId,
                'numero'      => 'FAC-' . str_pad((string) $factureId, 6, '0', STR_PAD_LEFT),
                'montant'     => $montant,
                'commande_id' => $commandeId,
            ]
        );

        return $factureId;
    }
}
