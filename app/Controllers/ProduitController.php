<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ProduitModel;

class ProduitController extends Controller
{
    private ProduitModel $produitModel;

    public function __construct()
    {
        // Écran de gestion : réservé au gestionnaire connecté. Le routeur crée
        // l'objet avant d'appeler l'action, donc toutes les méthodes du
        // contrôleur sont protégées par cette seule ligne.
        authGestionnaire();

        $this->produitModel = new ProduitModel();
    }

    /**
     * Liste des produits, avec recherche par libellé.
     */
    public function index(): void
    {
        $libelle = trim($_GET['libelle'] ?? '');

        $produits = $libelle !== ''
            ? $this->produitModel->searchByLibelle($libelle)
            : $this->produitModel->allProduits();

        loadView('produits/index', [
            'title'    => 'Produits',
            'produits' => $produits,
            'libelle'  => $libelle,
            'flash'    => $this->getFlash(),
        ]);
    }

    /**
     * Affiche le formulaire d'ajout.
     */
    public function create(): void
    {
        loadView('produits/form', [
            'title'   => 'Nouveau produit',
            'donnees' => [],
            'errors'  => [],
        ]);
    }

    /**
     * Valide puis enregistre le produit.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('produit', 'create');
        }

        $donnees = [
            'reference' => trim($_POST['reference'] ?? ''),
            'libelle'   => trim($_POST['libelle'] ?? ''),
            'quantite'  => trim($_POST['quantite'] ?? ''),
            'prix'      => trim($_POST['prix'] ?? ''),
        ];

        $errors = validDataProduit($donnees);

        // Contrôle qui nécessite la base : unicité de la référence.
        if (!isset($errors['reference']) && $this->produitModel->findByReference($donnees['reference'])) {
            $errors['reference'] = 'Cette référence est déjà utilisée par un autre produit';
        }

        if ($errors) {
            loadView('produits/form', [
                'title'   => 'Nouveau produit',
                'donnees' => $donnees,
                'errors'  => $errors,
            ]);
            return;
        }

        $this->produitModel->createProduit($donnees);

        $this->setFlash('succes', "Le produit {$donnees['libelle']} a été ajouté.");
        redirectTo('produit', 'index');
    }

    /**
     * Mise à jour rapide de la quantité en stock (formulaire inline dans la liste).
     */
    public function updateStock(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('produit', 'index');
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $qte_stock = (int) ($_POST['qte_stock'] ?? -1);

        if ($id <= 0 || $qte_stock < 0) {
            $this->setFlash('erreur', 'Quantité invalide.');
            redirectTo('produit', 'index');
        }

        $this->produitModel->updateStock($id, $qte_stock);

        $this->setFlash('succes', 'Le stock a été mis à jour.');
        redirectTo('produit', 'index');
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