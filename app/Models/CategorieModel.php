<?php
namespace App\Models;

use App\Core\Model;

class CategorieModel extends Model
{
    protected $table = 'categorie';

    /**
     * Cas d'utilisation « lister categories ».
     */
    public function allCategories(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY libelle");

        return $stmt->fetchAll();
    }

    public function findCategorie(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    /**
     * Contrôle d'unicité du libellé (évite les doublons "Informatique" / "informatique").
     */
    public function findByLibelle(string $libelle)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE libelle ILIKE ?");
        $stmt->execute([trim($libelle)]);

        return $stmt->fetch();
    }

    /**
     * Cas d'utilisation « ajouter categorie ».
     */
    public function createCategorie(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (libelle) VALUES (:libelle)");

        return $stmt->execute(['libelle' => trim($data['libelle'])]);
    }

    /**
     * Cas d'utilisation « modifier categorie ».
     */
    public function updateCategorie(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET libelle = :libelle WHERE id = :id");

        return $stmt->execute([
            'id'      => $id,
            'libelle' => trim($data['libelle']),
        ]);
    }

    /**
     * Cas d'utilisation « supprimer categorie ».
     */
    public function deleteCategorie(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");

        return $stmt->execute([$id]);
    }

    /**
     * produit.categorie_id référence categorie(id) : on compte les produits
     * rattachés avant suppression, pour un message clair plutôt qu'une erreur SQL
     * de contrainte de clé étrangère.
     */
    public function countProduits(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM produit WHERE categorie_id = ?");
        $stmt->execute([$id]);

        return (int) $stmt->fetchColumn();
    }
}
