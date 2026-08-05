<?php
namespace App\Models;

use App\Core\Model;

class ProduitCommandeModel extends Model
{
    protected $table = 'ligne_commande';

    /**
     * Lignes d'une commande donnée, avec les informations du produit.
     *
     * La colonne s'appelle « prix » dans la table (c'est le prix figé au moment
     * de la commande) : on la renomme en prix_unitaire, le nom utilisé dans les
     * vues et dans le reste du code.
     */
    public function findByCommande(int $commandeId): array
    {
        $sql = "SELECT pc.*,
                       pc.prix     AS prix_unitaire,
                       p.libelle   AS produit_libelle,
                       p.reference AS produit_reference,
                       p.qte_stock AS produit_qte_stock
                FROM {$this->table} pc
                JOIN produit p ON p.id = pc.produit_id
                WHERE pc.commande_id = ?
                ORDER BY pc.id";

        return $this->executeSelect($sql, [$commandeId]);
    }

    /**
     * Insère une ligne (produit + quantité + prix figé) pour une commande.
     */
    public function create(int $commandeId, int $produitId, int $quantite, float $prix): bool
    {
        $sql = "INSERT INTO {$this->table} (quantite, prix, commande_id, produit_id)
                VALUES (:quantite, :prix, :commande_id, :produit_id)";

        return $this->executeUpdate($sql, [
            'quantite'    => $quantite,
            'prix'        => $prix,
            'commande_id' => $commandeId,
            'produit_id'  => $produitId,
        ]);
    }

    /**
     * Vide une commande de toutes ses lignes.
     * Utilisé par la modification : les anciennes lignes sont remplacées par
     * les nouvelles (voir CommandeModel::updateCommande()).
     */
    public function deleteByCommande(int $commandeId): bool
    {
        return $this->executeUpdate(
            "DELETE FROM {$this->table} WHERE commande_id = ?",
            [$commandeId]
        );
    }
}
