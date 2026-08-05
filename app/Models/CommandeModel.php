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

        return $this->executeSelect($sql);
    }

    /**
     * Commandes d'un seul client : c'est ce que voit le client connecté dans
     * son espace. Le filtrage se fait ici, en SQL, et pas dans la vue : un
     * client ne doit jamais recevoir les commandes des autres.
     */
    public function allCommandesByClient(int $clientId): array
    {
        $sql = "SELECT c.*, u.nom AS client_nom, u.prenom AS client_prenom
                FROM {$this->table} c
                JOIN utilisateur u ON u.id = c.client_id
                WHERE c.client_id = ?
                ORDER BY c.date DESC, c.id DESC";

        return $this->executeSelect($sql, [$clientId]);
    }

    public function findCommande(int $id)
    {
        $sql = "SELECT c.*, u.nom AS client_nom, u.prenom AS client_prenom, u.telephone AS client_telephone
                FROM {$this->table} c
                JOIN utilisateur u ON u.id = c.client_id
                WHERE c.id = ?";

        return $this->executeSelectOne($sql, [$id]);
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
            // La colonne numero est NOT NULL : impossible d'insérer la commande
            // puis de la numéroter après coup. Or le numéro est construit à
            // partir de l'id... que PostgreSQL n'attribue qu'à l'insertion.
            $suivant = $this->executeSelectOne(
                "SELECT nextval(pg_get_serial_sequence('{$this->table}', 'id')) AS id"
            );

            $commandeId = (int) $suivant->id;
            $numero     = 'CMD-' . str_pad((string) $commandeId, 6, '0', STR_PAD_LEFT);

            $this->executeUpdate(
                "INSERT INTO {$this->table} (id, numero, date, montant_total, validee, client_id)
                 VALUES (:id, :numero, CURRENT_DATE, 0, false, :client_id)",
                [
                    'id'        => $commandeId,
                    'numero'    => $numero,
                    'client_id' => $clientId,
                ]
            );

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

            // Le total n'est connu qu'après avoir parcouru toutes les lignes.
            $this->executeUpdate(
                "UPDATE {$this->table} SET montant_total = :montant WHERE id = :id",
                [
                    'montant' => $montantTotal,
                    'id'      => $commandeId,
                ]
            );

            $this->db->commit();

            return $commandeId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Cas d'utilisation « modifier commande ».
     */
    public function updateCommande(int $commandeId, int $clientId, array $lignes): bool
    {
        $this->db->beginTransaction();

        try {
            $produitCommandeModel = new ProduitCommandeModel();
            $produitModel         = new ProduitModel();

            // 1. Rendre au stock les quantités des anciennes lignes.
            foreach ($produitCommandeModel->findByCommande($commandeId) as $ancienne) {
                $produitModel->updateStock(
                    (int) $ancienne->produit_id,
                    (int) $ancienne->produit_qte_stock + (int) $ancienne->quantite
                );
            }

            // 2. Vider la commande de ses lignes.
            $produitCommandeModel->deleteByCommande($commandeId);

            // 3. Enregistrer les nouvelles lignes et recalculer le total.
            $montantTotal = 0;

            foreach ($lignes as $ligne) {
                $produit  = $ligne['produit'];
                $quantite = (int) $ligne['quantite'];
                $prix     = (float) $produit->prix_unitaire;

                $produitCommandeModel->create($commandeId, $produit->id, $quantite, $prix);

                $produitAJour = $produitModel->findByReference($produit->reference);
                $produitModel->updateStock($produit->id, (int) $produitAJour->qte_stock - $quantite);

                $montantTotal += $quantite * $prix;
            }

            $this->executeUpdate(
                "UPDATE {$this->table}
                 SET montant_total = :montant, client_id = :client_id
                 WHERE id = :id",
                [
                    'montant'   => $montantTotal,
                    'client_id' => $clientId,
                    'id'        => $commandeId,
                ]
            );

            $this->db->commit();

            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * facture.commande_id est UNIQUE + ON DELETE RESTRICT
     */
    public function countFactures(int $commandeId): int
    {
        $ligne = $this->executeSelectOne(
            "SELECT COUNT(*) AS nb FROM facture WHERE commande_id = ?",
            [$commandeId]
        );

        return (int) $ligne->nb;
    }

    /**
     * Cas d'utilisation « supprimer commande ».
     */
    public function deleteCommande(int $id): bool
    {
        return $this->executeUpdate("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}
