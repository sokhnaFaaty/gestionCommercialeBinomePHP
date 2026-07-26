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
            // RETURNING id est propre à PostgreSQL : l'INSERT renvoie l'id créé.
            $creee = $this->executeSelectOne(
                "INSERT INTO {$this->table} (date, montant_total, validee, client_id)
                 VALUES (CURRENT_DATE, 0, false, :client_id)
                 RETURNING id",
                ['client_id' => $clientId]
            );

            $commandeId = (int) $creee->id;

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

            $this->executeUpdate(
                "UPDATE {$this->table} SET numero = :numero, montant_total = :montant WHERE id = :id",
                [
                    'numero'  => $numero,
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
     *
     * Le contenu de la commande est entièrement remplacé. En trois temps :
     *   1. on rend au stock ce que les anciennes lignes avaient pris ;
     *   2. on supprime ces anciennes lignes ;
     *   3. on enregistre les nouvelles, exactement comme à la création.
     *
     * Le tout dans une transaction : si une étape échoue, rollBack() ramène la
     * base à son état de départ. Sans ça, on pourrait se retrouver avec du
     * stock rendu mais des lignes toujours présentes.
     *
     * @param array $lignes Tableau de ['produit' => object produit, 'quantite' => int]
     *                      Déjà validés par le controller avant l'appel.
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

                // Attention : $produit->qte_stock a été lu AVANT l'étape 1.
                // On relit donc le stock à jour avant de le décrémenter.
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
     * facture.commande_id est UNIQUE + ON DELETE RESTRICT : on vérifie qu'aucune
     * facture n'est rattachée avant de permettre la suppression de la commande.
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
     * Les lignes produit_commande sont supprimées automatiquement (ON DELETE CASCADE).
     */
    public function deleteCommande(int $id): bool
    {
        return $this->executeUpdate("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}
