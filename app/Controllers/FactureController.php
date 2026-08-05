<?php
namespace App\Models;

use App\Core\Model;

class FactureModel extends Model
{
    protected $table = 'facture';

    /**
     * Le statut n'est pas stocké sur la facture : il est recalculé à partir
     * de la somme des paiements reçus, comparée au montant de la facture.
     * Les valeurs renvoyées correspondent exactement à l'enum type_statut_paiement.
     */
    private const SELECT_AVEC_STATUT = "
        SELECT f.*,
               c.numero AS commande_numero,
               u.id AS client_id, u.nom AS client_nom, u.prenom AS client_prenom,
               COALESCE(p.paye, 0) AS montant_paye,
               CASE
                   WHEN COALESCE(p.paye, 0) = 0 THEN 'non payee'
                   WHEN COALESCE(p.paye, 0) < f.montant THEN 'partiellement_payee'
                   ELSE 'totalement_payee'
               END AS statut
        FROM facture f
        JOIN commande c ON c.id = f.commande_id
        JOIN utilisateur u ON u.id = c.client_id
        LEFT JOIN (
            SELECT facture_id, SUM(montant_verse) AS paye
            FROM paiement
            GROUP BY facture_id
        ) p ON p.facture_id = f.id
    ";

    /**
     * Cas d'utilisation « afficher tous les factures ».
     */
    public function allFactures(): array
    {
        return $this->executeSelect(self::SELECT_AVEC_STATUT . " ORDER BY f.date DESC, f.id DESC");
    }

    /**
     * Cas d'utilisation « afficher les factures impayées / soldées ».
     * $statut attend une des valeurs de type_statut_paiement.
     */
    public function allFacturesByStatut(string $statut): array
    {
        $sql = "SELECT * FROM (" . self::SELECT_AVEC_STATUT . ") AS f
                WHERE statut = ?
                ORDER BY date DESC, id DESC";

        return $this->executeSelect($sql, [$statut]);
    }

    public function findFacture(int $id)
    {
        return $this->executeSelectOne(self::SELECT_AVEC_STATUT . " WHERE f.id = ?", [$id]);
    }

    /**
     * facture.commande_id est UNIQUE : sert à vérifier qu'une commande n'a
     * pas déjà sa facture avant d'en générer une nouvelle.
     */
    public function findByCommande(int $commandeId)
    {
        return $this->executeSelectOne(
            "SELECT * FROM {$this->table} WHERE commande_id = ?",
            [$commandeId]
        );
    }

    /**
     * Génère la facture d'une commande. Le montant reprend toujours
     * commande.montant_total (voir FactureController::store()).
     */
    public function createFacture(int $commandeId, float $montant): int
    {
        // numero est NOT NULL : impossible d'insérer puis de numéroter après
        // coup. On demande l'id à la séquence avant l'insertion, comme pour
        // commande.numero (voir CommandeModel::createCommande()).
        $suivant = $this->executeSelectOne(
            "SELECT nextval(pg_get_serial_sequence('{$this->table}', 'id')) AS id"
        );

        $factureId = (int) $suivant->id;
        $numero    = 'FACT-' . str_pad((string) $factureId, 6, '0', STR_PAD_LEFT);

        $this->executeUpdate(
            "INSERT INTO {$this->table} (id, numero, date, montant, commande_id)
             VALUES (:id, :numero, CURRENT_DATE, :montant, :commande_id)",
            [
                'id'          => $factureId,
                'numero'      => $numero,
                'montant'     => $montant,
                'commande_id' => $commandeId,
            ]
        );

        return $factureId;
    }
}