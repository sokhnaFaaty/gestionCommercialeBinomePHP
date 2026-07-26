<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ClientModel;

class ClientController extends Controller
{
    private ClientModel $clientModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
    }

    /**
     * Liste des clients, avec recherche par téléphone.
     */
    public function index(): void
    {
        $telephone = trim($_GET['telephone'] ?? '');

        $clients = $telephone !== ''
            ? $this->clientModel->searchByTelephone($telephone)
            : $this->clientModel->allOrdered();

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
            redirectTo('client', 'create');
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
        if (!isset($errors['telephoneVide']) && $this->clientModel->findByTelephone($donnees['telephone'])) {
            $errors['telephoneVide'] = 'Ce numéro est déjà utilisé par un autre client';
        }

        if (!isset($errors['email'])) {
            if (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "L'adresse email n'est pas valide";
            } elseif ($this->clientModel->findByEmail($donnees['email'])) {
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

        $this->clientModel->create($donnees);

        $this->setFlash('succes', "Le client {$donnees['prenom']} {$donnees['nom']} a été ajouté.");
        redirectTo('client', 'index');
    }

    /**
     * Supprime un client (en POST uniquement, pour qu'un simple lien ne puisse pas supprimer).
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('client', 'index');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $client = $id > 0 ? $this->clientModel->find($id) : null;

        if (!$client) {
            $this->setFlash('erreur', 'Client introuvable.');
            redirectTo('client', 'index');
        }

        // La clé étrangère commande.client_id est en ON DELETE RESTRICT.
        $nbCommandes = $this->clientModel->countCommandes($id);

        if ($nbCommandes > 0) {
            $this->setFlash(
                'erreur',
                "Impossible de supprimer {$client->prenom} {$client->nom} : "
                . "ce client a {$nbCommandes} commande(s) enregistrée(s)."
            );
            redirectTo('client', 'index');
        }

        $this->clientModel->delete($id);

        $this->setFlash('succes', "Le client {$client->prenom} {$client->nom} a été supprimé.");
        redirectTo('client', 'index');
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
