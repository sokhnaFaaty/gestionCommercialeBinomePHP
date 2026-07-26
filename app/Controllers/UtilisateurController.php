<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UtilisateurModel;

/**
 * Gestion des utilisateurs, réservée au gestionnaire.
 *
 * Depuis le passage à la table unique utilisateur, un client est un
 * utilisateur de role 'client' : ces écrans ne listent donc que ceux-là.
 */
class UtilisateurController extends Controller
{
    private UtilisateurModel $utilisateurModel;

    public function __construct()
    {
        // Écran de gestion : réservé au gestionnaire connecté. Le routeur crée
        // l'objet avant d'appeler l'action, donc toutes les méthodes du
        // contrôleur sont protégées par cette seule ligne.
        authGestionnaire();

        $this->utilisateurModel = new UtilisateurModel();
    }

    /**
     * Liste des clients, avec recherche par téléphone.
     */
    public function index(): void
    {
        $telephone = trim($_GET['telephone'] ?? '');

        $clients = $telephone !== ''
            ? $this->utilisateurModel->searchClientsByTelephone($telephone)
            : $this->utilisateurModel->allClients();

        loadView('clients/index', [
            'title'     => 'Clients',
            'clients'   => $clients,
            'telephone' => $telephone,
            'flash'     => $this->getFlash(),
        ]);
    }

    /**
     * Affiche le formulaire d'ajout.
     */
    public function create(): void
    {
        loadView('clients/form', [
            'title'   => 'Nouveau client',
            'donnees' => [],
            'errors'  => [],
        ]);
    }

    /**
     * Valide puis enregistre le client.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('utilisateur', 'create');
        }

        $donnees = [
            'nom'       => trim($_POST['nom'] ?? ''),
            'prenom'    => trim($_POST['prenom'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'password'  => $_POST['password'] ?? '',
        ];

        $errors = validDataClient($donnees);

        // Contrôles qui nécessitent la base : unicité du téléphone et de l'email.
        // Les deux sont uniques sur toute la table, gestionnaires compris.
        if (!isset($errors['telephoneVide']) && $this->utilisateurModel->findByTelephone($donnees['telephone'])) {
            $errors['telephoneVide'] = 'Ce numéro est déjà utilisé par un autre compte';
        }

        if (!isset($errors['email'])) {
            if (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "L'adresse email n'est pas valide";
            } elseif ($this->utilisateurModel->findByEmail($donnees['email'])) {
                $errors['email'] = 'Cette adresse email est déjà utilisée';
            }
        }

        if ($errors) {
            loadView('clients/form', [
                'title'   => 'Nouveau client',
                'donnees' => $donnees,
                'errors'  => $errors,
            ]);
            return;
        }

        $this->utilisateurModel->createClient($donnees);

        $this->setFlash('succes', "Le client {$donnees['prenom']} {$donnees['nom']} a été ajouté.");
        redirectTo('utilisateur', 'index');
    }

    /**
     * Supprime un client (en POST uniquement, pour qu'un simple lien ne puisse pas supprimer).
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('utilisateur', 'index');
        }

        $id = (int) ($_POST['id'] ?? 0);

        // findClient() ne retourne rien pour un gestionnaire : cet écran ne
        // peut donc pas servir à supprimer un compte de gestion.
        $client = $id > 0 ? $this->utilisateurModel->findClient($id) : null;

        if (!$client) {
            $this->setFlash('erreur', 'Client introuvable.');
            redirectTo('utilisateur', 'index');
        }

        // La clé étrangère commande.client_id est en ON DELETE RESTRICT.
        $nbCommandes = $this->utilisateurModel->countCommandes($id);

        if ($nbCommandes > 0) {
            $this->setFlash(
                'erreur',
                "Impossible de supprimer {$client->prenom} {$client->nom} : "
                . "ce client a {$nbCommandes} commande(s) enregistrée(s)."
            );
            redirectTo('utilisateur', 'index');
        }

        $this->utilisateurModel->deleteClient($id);

        $this->setFlash('succes', "Le client {$client->prenom} {$client->nom} a été supprimé.");
        redirectTo('utilisateur', 'index');
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
