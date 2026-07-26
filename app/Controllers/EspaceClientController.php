<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\CommandeModel;
use App\Models\ProduitCommandeModel;

/**
 * Espace du client connecté : il consulte SES commandes, en lecture seule.
 *
 * Aucune méthode ne fait confiance à un identifiant venu de l'URL pour
 * décider à qui appartient une commande : la référence, c'est toujours
 * l'id en session.
 */
class EspaceClientController extends Controller
{
    private CommandeModel $commandeModel;
    private ProduitCommandeModel $produitCommandeModel;

    public function __construct()
    {
        // Barrière : le constructeur est appelé par le routeur avant l'action,
        // donc tout l'écran est protégé d'un coup.
        authClient();

        $this->commandeModel        = new CommandeModel();
        $this->produitCommandeModel = new ProduitCommandeModel();
    }

    /**
     * Liste des commandes du client connecté.
     */
    public function index(): void
    {
        $client = utilisateurConnecte();

        loadView('espace/index', [
            'title'     => 'Mes commandes',
            'commandes' => $this->commandeModel->allCommandesByClient((int) $client['id']),
            'client'    => $client,
            'flash'     => $this->getFlash(),
        ]);
    }

    /**
     * Détail d'une de ses commandes.
     */
    public function show(int $id): void
    {
        $client   = utilisateurConnecte();
        $commande = $this->commandeModel->findCommande($id);

        // Le contrôle essentiel : la commande existe ET elle appartient bien
        // à la personne connectée. Sans cette deuxième condition, changer le
        // numéro dans l'URL suffirait à lire les commandes des autres.
        if (!$commande || (int) $commande->client_id !== (int) $client['id']) {
            $this->setFlash('erreur', 'Commande introuvable.');
            redirectTo('espace', 'index');
        }

        // La vue du détail est celle du gestionnaire : elle est déjà en lecture
        // seule. On lui indique seulement vers où pointe « Retour à la liste ».
        loadView('commandes/show', [
            'title'    => 'Commande ' . $commande->numero,
            'commande' => $commande,
            'lignes'   => $this->produitCommandeModel->findByCommande($id),
            'retour'   => path('espace', 'index'),
        ]);
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
