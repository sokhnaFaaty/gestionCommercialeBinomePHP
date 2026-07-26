<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UtilisateurModel;

/**
 * Connexion et déconnexion.
 *
 * Seul contrôleur accessible sans être connecté : c'est ici qu'on entre
 * dans l'application. Les autres écrans appellent authGestionnaire() ou
 * authClient() dans leur constructeur.
 */
class AuthController extends Controller
{
    private UtilisateurModel $utilisateurModel;

    public function __construct()
    {
        $this->utilisateurModel = new UtilisateurModel();
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function login(): void
    {
        // Déjà connecté : inutile de redemander, on va directement à l'accueil
        // correspondant au rôle.
        if (isConnected()) {
            $this->redirigerSelonRole();
        }

        loadView('auth/login', [
            'title'   => 'Connexion',
            'donnees' => [],
            'errors'  => [],
            'flash'   => $this->getFlash(),
        ]);
    }

    /**
     * Vérifie l'email et le mot de passe, puis ouvre la session.
     */
    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('auth', 'login');
        }

        $donnees = [
            'email'    => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
        ];

        $errors      = validDataLogin($donnees);
        $utilisateur = null;

        if (!$errors) {
            $utilisateur = $this->utilisateurModel->findByEmail($donnees['email']);

            // Comparaison directe : les mots de passe sont pour l'instant
            // enregistrés en clair dans la table utilisateur.
            //
            // TODO (sécurité) : les hacher. Trois changements suffisent —
            //   1. ici              : password_verify($donnees['password'], $utilisateur->password)
            //   2. createClient()   : password_hash($data['password'], PASSWORD_DEFAULT)
            //   3. les comptes déjà en base : les convertir une bonne fois.
            if (!$utilisateur || $utilisateur->password !== $donnees['password']) {
                // Message volontairement vague : préciser « cet email n'existe
                // pas » confirmerait à un inconnu quels comptes existent.
                $errors['identifiants'] = "Email ou mot de passe incorrect";
            }
        }

        if ($errors) {
            loadView('auth/login', [
                'title'   => 'Connexion',
                'donnees' => $donnees,
                'errors'  => $errors,
                'flash'   => null,
            ]);
            return;
        }

        // Nouvel identifiant de session à la connexion : si quelqu'un avait
        // réussi à imposer un identifiant de session à la victime avant sa
        // connexion, il devient inutilisable (attaque dite « fixation de session »).
        session_regenerate_id(true);

        // On ne met JAMAIS le mot de passe en session, même haché.
        $_SESSION['user'] = [
            'id'     => (int) $utilisateur->id,
            'nom'    => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email'  => $utilisateur->email,
            'role'   => $utilisateur->role,
        ];

        $this->redirigerSelonRole();
    }

    /**
     * Ferme la session (en POST : un simple lien ne doit pas déconnecter).
     */
    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('auth', 'login');
        }

        // Vider puis détruire : $_SESSION = [] efface les données,
        // session_destroy() supprime la session côté serveur.
        $_SESSION = [];
        session_destroy();

        // On redémarre une session vide, uniquement pour pouvoir y déposer le
        // message de confirmation affiché sur la page de connexion.
        session_start();
        session_regenerate_id(true);

        $this->setFlash('succes', 'Vous êtes déconnecté.');
        redirectTo('auth', 'login');
    }

    // ---------------------------------------------------------------------

    /**
     * Chaque rôle a son écran d'accueil.
     */
    private function redirigerSelonRole(): void
    {
        if (hasRole(ROLE_GESTIONNAIRE)) {
            redirectTo('utilisateur', 'index');
        }

        redirectTo('espace', 'index');
    }

    private function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    private function getFlash(): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }
}
