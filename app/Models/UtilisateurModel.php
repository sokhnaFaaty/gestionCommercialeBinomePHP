<?php
namespace App\Models;

use App\Core\Model;

/**
 * Une seule table pour les deux types de comptes.
 * C'est la colonne role qui distingue un client d'un gestionnaire.
 */
class UtilisateurModel extends Model
{
    protected $table = 'utilisateur';

    public const ROLE_CLIENT       = 'client';
    public const ROLE_GESTIONNAIRE = 'gestionnaire';

    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([trim($email)]);

        return $stmt->fetch();
    }

    /**
     * Vérifie le couple email / mot de passe.
     * Retourne l'utilisateur si la connexion est valide, false sinon.
     *
     * TODO (avant la soutenance) : les mots de passe sont en clair.
     * Une fois hachés, remplacer la comparaison par password_verify().
     */
    public function verifierConnexion(string $email, string $motDePasse)
    {
        $utilisateur = $this->findByEmail($email);

        if (!$utilisateur) {
            return false;
        }

        return hash_equals($utilisateur->password, $motDePasse) ? $utilisateur : false;
    }

    public function findByTelephone(string $telephone)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE telephone = ?");
        $stmt->execute([trim($telephone)]);

        return $stmt->fetch();
    }
}
