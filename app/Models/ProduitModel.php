<?php
namespace App\Models;

use App\Core\Model;

// FICHIER TEMPORAIRE DE TEST - sera restaure a vide juste apres
class ProduitModel extends Model
{
    protected $table = 'produit';

    public const STATUT_DISPONIBLE = 'disponible';
    public const STATUT_RUPTURE    = 'rupture';

    /**
     * Cas d'utilisation « lister produits ».
     */
    public function allProduits(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY libelle");

        return $stmt->fetchAll();
    }

    /**
     * Cas d'utilisation « rechercher produit par libellé ».
     */
    public function searchByLibelle(string $libelle): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE libelle ILIKE ? ORDER BY libelle"
        );
        $stmt->execute(['%' . trim($libelle) . '%']);

        return $stmt->fetchAll();
    }

    /**
     * La référence est unique : contrôle utilisé par le controller avant insertion.
     */
    public function findByReference(string $reference)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE reference = ?");
        $stmt->execute([trim($reference)]);

        return $stmt->fetch();
    }

    /**
     * Cas d'utilisation « ajouter produit ».
     */
    public function createProduit(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (reference, libelle, qtsock, prix_unitaire, statut)
                VALUES (:reference, :libelle, :qtsock, :prix_unitaire, :statut)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'reference'     => trim($data['reference']),
            'libelle'       => trim($data['libelle']),
            'qtsock'        => (int) $data['quantite'],
            'prix_unitaire' => (float) $data['prix'],
            'statut'        => $this->determinerStatut((int) $data['quantite']),
        ]);
    }

    /**
     * Cas d'utilisation « mettre à jour la quantité en stock ».
     * Le statut est recalculé à chaque changement de stock.
     */
    public function updateStock(int $id, int $qtsock): bool
    {
        $sql = "UPDATE {$this->table} SET qtsock = :qtsock, statut = :statut WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'     => $id,
            'qtsock' => $qtsock,
            'statut' => $this->determinerStatut($qtsock),
        ]);
    }

    private function determinerStatut(int $qtsock): string
    {
        return $qtsock > 0 ? self::STATUT_DISPONIBLE : self::STATUT_RUPTURE;
    }
}
