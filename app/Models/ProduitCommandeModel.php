<?php
namespace App\Models;

use App\Core\Model;

class ProduitCommandeModel extends Model
{
    protected $table = 'produit_commande';

    /**
     * Lignes d'une commande donnée, avec le libellé et la référence du produit.
     */
    public function findByCommande(int $commandeId): array
    {
        $sql = "SELECT pc.*, p.libelle AS produit_libelle, p.reference AS produit_reference
                FROM {$this->table} pc
                JOIN produit p ON p.id = pc.produit_id
                WHERE pc.commande_id = ?
                ORDER BY pc.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$commandeId]);

        return $stmt->fetchAll();
    }

    /**
     * Insère une ligne (produit + quantité + prix figé) pour une commande.
     */
    public function create(int $commandeId, int $produitId, int $quantite, float $prix): bool
    {
        $sql = "INSERT INTO {$this->table} (quantite, prix, commande_id, produit_id)
                VALUES (:quantite, :prix, :commande_id, :produit_id)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'quantite'    => $quantite,
            'prix'        => $prix,
            'commande_id' => $commandeId,
            'produit_id'  => $produitId,
        ]);
    }
}