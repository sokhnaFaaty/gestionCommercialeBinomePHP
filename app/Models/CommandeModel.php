<?php
namespace App\Models;

use App\Core\Model;
use PDOException;

class CommandeModel extends Model
{
    protected $table = 'commande';

    public function allCommandes(): array
    {
        $sql = "SELECT c.*, u.nom AS client_nom, u.prenom AS client_prenom
                FROM {$this->table} c
                JOIN utilisateur u ON u.id = c.client_id
                ORDER BY c.date DESC, c.id DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function findCommande(int $id)
    {
        $sql = "SELECT c.*, u.nom AS client_nom, u.prenom AS client_prenom, u.telephone AS client_telephone
                FROM {$this->table} c
                JOIN utilisateur u ON u.id = c.client_id
                WHERE c.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    /**
     * Cas d'utilisation « créer commande » (panier multi-produits).
     *
     * @param array $lignes Tableau de ['produit' => object produit, 'quantite' => int]
     *                      Déjà validés par le controller avant l'appel.
     */
    public function createCommande(int $clientId, array $lignes): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} (date, montant_total, validee, client_id)
                 VALUES (CURRENT_DATE, 0, false, :client_id)
                 RETURNING id"
            );
            $stmt->execute(['client_id' => $clientId]);
            $commandeId = (int) $stmt->fetchColumn();

            $produitCommandeModel = new ProduitCommandeModel();
            $produitModel         = new ProduitModel();

            $montantTotal = 0;

            foreach ($lignes as $ligne) {
                $produit  = $ligne['produit'];
                $quantite = (int) $ligne['quantite'];
                $prix     = (float) $produit->prix_unitaire;

                $produitCommandeModel->create($commandeId, $produit->id, $quantite, $prix);

                $nouveauStock = (int) $produit->qte_stock - $quantite;
                $produitModel->updateStock($produit->id, $nouveauStock);

                $montantTotal += $quantite * $prix;
            }

            $numero = 'CMD-' . str_pad((string) $commandeId, 6, '0', STR_PAD_LEFT);

            $stmtMaj = $this->db->prepare(
                "UPDATE {$this->table} SET numero = :numero, montant_total = :montant WHERE id = :id"
            );
            $stmtMaj->execute([
                'numero'  => $numero,
                'montant' => $montantTotal,
                'id'      => $commandeId,
            ]);

            $this->db->commit();

            return $commandeId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * facture.commande_id est UNIQUE + ON DELETE RESTRICT : on vérifie qu'aucune
     * facture n'est rattachée avant de permettre la suppression de la commande.
     */
    public function countFactures(int $commandeId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM facture WHERE commande_id = ?");
        $stmt->execute([$commandeId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Cas d'utilisation « supprimer commande ».
     * Les lignes produit_commande sont supprimées automatiquement (ON DELETE CASCADE).
     */
    public function deleteCommande(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");

        return $stmt->execute([$id]);
    }
}

