<?php
namespace App\Models;

use App\Core\Model;

class ClientModel extends Model
{
    protected $table = 'client';

    /**
     * Enregistre un nouveau client.
     *
     * TODO (branche auth) : hacher le mot de passe avec password_hash()
     * et vérifier avec password_verify() à la connexion.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (nom, prenom, telephone, email, password, role)
                VALUES (:nom, :prenom, :telephone, :email, :password, :role)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nom'       => trim($data['nom']),
            'prenom'    => trim($data['prenom']),
            'telephone' => trim($data['telephone']),
            'email'     => trim($data['email']),
            'password'  => $data['password'],
            'role'      => $data['role'] ?? 'client',
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table}
                SET nom = :nom, prenom = :prenom, telephone = :telephone, email = :email
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'        => $id,
            'nom'       => trim($data['nom']),
            'prenom'    => trim($data['prenom']),
            'telephone' => trim($data['telephone']),
            'email'     => trim($data['email']),
        ]);
    }

    /**
     * Recherche exacte, utilisée pour le contrôle d'unicité.
     */
    public function findByTelephone(string $telephone)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE telephone = ?");
        $stmt->execute([trim($telephone)]);

        return $stmt->fetch();
    }

    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([trim($email)]);

        return $stmt->fetch();
    }

    /**
     * Recherche partielle : cas d'utilisation « rechercher client par téléphone ».
     */
    public function searchByTelephone(string $telephone): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE telephone LIKE ? ORDER BY nom, prenom"
        );
        $stmt->execute(['%' . trim($telephone) . '%']);

        return $stmt->fetchAll();
    }

    public function allOrdered(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY nom, prenom")->fetchAll();
    }

    /**
     * commande.client_id est en ON DELETE RESTRICT : on compte les commandes
     * avant de supprimer, pour afficher un message clair au lieu d'une erreur SQL.
     */
    public function countCommandes(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM commande WHERE client_id = ?");
        $stmt->execute([$id]);

        return (int) $stmt->fetchColumn();
    }
}
