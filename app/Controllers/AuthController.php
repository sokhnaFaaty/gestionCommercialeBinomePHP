<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UtilisateurModel;

class AuthController extends Controller
{
    private UtilisateurModel $utilisateurModel;

    public function __construct()
    {
        $this->utilisateurModel = new UtilisateurModel();
    }

    /**
     * Accueil après connexion. Le contenu dépend du rôle.
     */
    public function index(): void
    {
        auth(); // helper : redirige vers /auth/login si personne n'est connecté

        loadView('auth/accueil', [
            'title' => 'Accueil',
            'user'  => $_SESSION['user'],
            'menu'  => self::menu($_SESSION['user']['role']),
        ]);
    }

    /**
     * Formulaire de connexion.
     */
    public function login(): void
    {
        if (isConnected()) {
            redirectTo('auth', 'index');
        }

        $this->afficherFormulaire('', null);
    }

    /**
     * Traitement du formulaire de connexion.
     */
    public function connexion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('auth', 'login');
        }

        $email      = trim($_POST['email'] ?? '');
        $motDePasse = $_POST['password'] ?? '';

        if ($email === '' || $motDePasse === '') {
            $this->afficherFormulaire($email, 'Veuillez saisir votre email et votre mot de passe.');
            return;
        }

        $utilisateur = $this->utilisateurModel->verifierConnexion($email, $motDePasse);

        if (!$utilisateur) {
            // Message identique que l'email soit inconnu ou le mot de passe faux :
            // sinon le formulaire permettrait de deviner quels comptes existent.
            $this->afficherFormulaire($email, 'Email ou mot de passe incorrect.');
            return;
        }

        // Nouvel identifiant de session à la connexion (contre la fixation de session).
        session_regenerate_id(true);

        // On ne met jamais le mot de passe en session.
        $_SESSION['user'] = [
            'id'     => $utilisateur->id,
            'nom'    => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email'  => $utilisateur->email,
            'role'   => $utilisateur->role,
        ];

        redirectTo('auth', 'index');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        redirectTo('auth', 'login');
    }

    /**
     * Page affichée quand un client tente d'ouvrir une page réservée au gestionnaire.
     */
    public function accesRefuse(): void
    {
        auth();

        http_response_code(403);

        loadView('auth/refuse', [
            'title' => 'Accès refusé',
            'user'  => $_SESSION['user'],
        ]);
    }

    private function afficherFormulaire(string $email, ?string $erreur): void
    {
        loadView('auth/login', [
            'title'  => 'Connexion',
            'email'  => $email,
            'erreur' => $erreur,
        ], 'auth');
    }

    /**
     * Entrées de menu selon le rôle. Publique et statique car la barre de
     * navigation (views/partials/header.php) s'en sert aussi : le menu n'est
     * donc décrit qu'à un seul endroit.
     *
     * « disponible » vaut false tant que le contrôleur ou l'action n'existe pas :
     * l'entrée s'affiche alors en grisé au lieu de mener à une 404. Elle
     * s'activera toute seule quand la fonctionnalité sera écrite.
     */
    public static function menu(string $role): array
    {
        $entrees = $role === UtilisateurModel::ROLE_GESTIONNAIRE
            ? [
                ['Clients',    'client',    'index', 'Ajouter, rechercher et supprimer des clients'],
                ['Produits',   'produit',   'index', 'Catalogue et quantités en stock'],
                ['Commandes',  'commande',  'index', 'Créer et suivre les commandes'],
                ['Catégories', 'categorie', 'index', 'Organiser les produits par catégorie'],
                ['Factures',   'facture',   'index', 'Consulter les factures et leur état'],
                ['Paiements',  'paiement',  'index', 'Enregistrer les paiements reçus'],
            ]
            : [
                ['Mes commandes', 'commande', 'mesCommandes', 'Retrouver toutes vos commandes'],
                ['Mes factures',  'facture',  'mesFactures',  'Vos factures, filtrables par état'],
                ['Mon profil',    'auth',     'profil',       'Modifier vos informations'],
            ];

        return array_map(function (array $entree): array {
            [$libelle, $controleur, $action, $description] = $entree;

            $classe = 'App\\Controllers\\' . ucfirst($controleur) . 'Controller';

            return [
                'libelle'     => $libelle,
                'controleur'  => $controleur,
                'action'      => $action,
                'description' => $description,
                'disponible'  => class_exists($classe) && method_exists($classe, $action),
            ];
        }, $entrees);
    }
}
