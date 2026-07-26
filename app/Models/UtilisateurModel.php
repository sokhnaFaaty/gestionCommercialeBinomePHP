<?php
namespace App\Models;

use App\Core\Model;

/**
 * Une seule table pour les deux types de comptes.
 * C'est la colonne role qui distingue un client d'un gestionnaire.
 *
 * Les méthodes d'authentification travaillent sur tous les utilisateurs ;
 * celles nommées « ...Client » filtrent sur role = 'client'.
 */
class UtilisateurModel extends Model
{
    protected $table = 'utilisateur';

    public const ROLE_CLIENT       = 'client';
    public const ROLE_GESTIONNAIRE = 'gestionnaire';

    // ---------------------------------------------------------------------
    // Recherches sur tous les utilisateurs
    // ---------------------------------------------------------------------

    /**
     * L'email est unique sur toute la table : ce contrôle ne filtre pas
     * sur le rôle.
     */
    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([trim($email)]);

        return $stmt->fetch();
    }

    /**
     * Le téléphone est unique sur toute la table, pas seulement chez les
     * clients : ce contrôle ne filtre donc pas sur le rôle.
     */
    public function findByTelephone(string $telephone)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE telephone = ?");
        $stmt->execute([trim($telephone)]);

        return $stmt->fetch();
    }

    // ---------------------------------------------------------------------
    // Gestion des clients (role = 'client')
    // ---------------------------------------------------------------------

    public function allClients(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE role = ? ORDER BY nom, prenom"
        );
        $stmt->execute([self::ROLE_CLIENT]);

        return $stmt->fetchAll();
    }

    /**
     * Recherche partielle : cas d'utilisation « rechercher client par téléphone ».
     */
    public function searchClientsByTelephone(string $telephone): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE role = ? AND telephone LIKE ?
             ORDER BY nom, prenom"
        );
        $stmt->execute([self::ROLE_CLIENT, '%' . trim($telephone) . '%']);

        return $stmt->fetchAll();
    }

    /**
     * Comme find(), mais ne retourne rien si l'id désigne un gestionnaire :
     * on ne veut pas qu'un gestionnaire soit modifiable depuis l'écran clients.
     */
    public function findClient(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? AND role = ?");
        $stmt->execute([$id, self::ROLE_CLIENT]);

        return $stmt->fetch();
    }

    public function createClient(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (nom, prenom, telephone, email, password, role)
                VALUES (:nom, :prenom, :telephone, :email, :password, :role)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nom'       => trim($data['nom']),
            'prenom'    => trim($data['prenom']),
            'telephone' => trim($data['telephone']),
            'email'     => trim($data['email']),
            // TODO (sécurité) : enregistrer password_hash($data['password'], PASSWORD_DEFAULT)
            // plutôt que le mot de passe en clair. Voir AuthController::authenticate().
            'password'  => $data['password'],
            'role'      => self::ROLE_CLIENT, // jamais depuis le formulaire
        ]);
    }

    public function updateClient(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table}
                SET nom = :nom, prenom = :prenom, telephone = :telephone, email = :email
                WHERE id = :id AND role = :role";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id'        => $id,
            'nom'       => trim($data['nom']),
            'prenom'    => trim($data['prenom']),
            'telephone' => trim($data['telephone']),
            'email'     => trim($data['email']),
            'role'      => self::ROLE_CLIENT,
        ]);
    }

    public function deleteClient(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ? AND role = ?");

        return $stmt->execute([$id, self::ROLE_CLIENT]);
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
