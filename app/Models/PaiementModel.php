<?php
namespace App\Models;

use App\Core\Model;

class PaiementModel extends Model
{
    protected $table = 'paiement';

    /**
     * Cas d'utilisation « lister paiements ».
     */
    public function allPaiements(): array
    {
        $sql = "SELECT pa.*, f.numero AS facture_numero,
                       u.nom AS client_nom, u.prenom AS client_prenom
                FROM {$this->table} pa
                JOIN facture f ON f.id = pa.facture_id
                JOIN commande c ON c.id = f.commande_id
                JOIN utilisateur u ON u.id = c.client_id
                ORDER BY pa.date DESC, pa.id DESC";

        return $this->executeSelect($sql);
    }

    /**
     * Cas d'utilisation « afficher les paiements d'une facture ».
     */
    public function findByFacture(int $factureId): array
    {
        return $this->executeSelect(
            "SELECT * FROM {$this->table} WHERE facture_id = ? ORDER BY date DESC, id DESC",
            [$factureId]
        );
    }

    public function totalPaye(int $factureId): float
    {
        $ligne = $this->executeSelectOne(
            "SELECT COALESCE(SUM(montant_verse), 0) AS total FROM {$this->table} WHERE facture_id = ?",
            [$factureId]
        );

        return (float) $ligne->total;
    }

    /**
     * Cas d'utilisation « enregistrer un paiement ».
     * Le statut enregistré sur CE paiement reflète l'état de la facture
     * après ce versement (paiement partiel ou soldant la facture).
     */
    public function createPaiement(int $factureId, float $montantVerse, float $montantFacture): int
    {
        $suivant = $this->executeSelectOne(
            "SELECT nextval(pg_get_serial_sequence('{$this->table}', 'id')) AS id"
        );

        $paiementId = (int) $suivant->id;
        $numero     = 'PAI-' . str_pad((string) $paiementId, 6, '0', STR_PAD_LEFT);

        $nouveauTotal = $this->totalPaye($factureId) + $montantVerse;
        $statut       = $nouveauTotal >= $montantFacture ? 'totalement_payee' : 'partiellement_payee';

        $this->executeUpdate(
            "INSERT INTO {$this->table} (id, numero, montant_verse, date, statut, facture_id)
             VALUES (:id, :numero, :montant_verse, CURRENT_DATE, :statut, :facture_id)",
            [
                'id'            => $paiementId,
                'numero'        => $numero,
                'montant_verse' => $montantVerse,
                'statut'        => $statut,
                'facture_id'    => $factureId,
            ]
        );

        return $paiementId;
    }
}