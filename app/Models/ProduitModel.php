<?php
namespace App\Models;

use App\Core\Model;

// FICHIER TEMPORAIRE DE TEST - sera restaure a vide juste apres
class ProduitModel extends Model
{
    protected $table = 'produit';

    public function findByReference(string $reference)
    {
        return $this->executeSelectOne("SELECT * FROM {$this->table} WHERE reference = ?", [trim($reference)]);
    }

    public function updateStock(int $id, int $qteStock): bool
    {
        return $this->executeUpdate(
            "UPDATE {$this->table} SET qte_stock = :q, statut = :s WHERE id = :id",
            ['q' => $qteStock, 's' => $qteStock > 0 ? 'disponible' : 'rupture', 'id' => $id]
        );
    }
}
