<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\CategorieModel;

class CategorieController extends Controller
{
    private CategorieModel $categorieModel;

    public function __construct()
    {
        $this->categorieModel = new CategorieModel();
    }

    /**
     * Liste des catégories.
     */
    public function index(): void
    {
        loadView('categories/index', [
            'title'      => 'Catégories',
            'categories' => $this->categorieModel->allCategories(),
            'flash'      => $this->getFlash(),
        ]);
    }

    /**
     * Affiche le formulaire d'ajout.
     */
    public function create(): void
    {
        loadView('categories/form', [
            'title'   => 'Nouvelle catégorie',
            'action'  => 'store',
            'donnees' => [],
            'errors'  => [],
        ]);
    }

    /**
     * Valide puis enregistre la catégorie.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('categorie', 'create');
        }

        $donnees = [
            'libelle' => trim($_POST['libelle'] ?? ''),
        ];

        $errors = validDataCategorie($donnees);

        if (!isset($errors['libelle']) && $this->categorieModel->findByLibelle($donnees['libelle'])) {
            $errors['libelle'] = 'Cette catégorie existe déjà';
        }

        if ($errors) {
            loadView('categories/form', [
                'title'   => 'Nouvelle catégorie',
                'action'  => 'store',
                'donnees' => $donnees,
                'errors'  => $errors,
            ]);
            return;
        }

        $this->categorieModel->createCategorie($donnees);

        $this->setFlash('succes', "La catégorie {$donnees['libelle']} a été ajoutée.");
        redirectTo('categorie', 'index');
    }

    /**
     * Affiche le formulaire de modification.
     */
    public function edit(int $id): void
    {
        $categorie = $this->categorieModel->findCategorie($id);

        if (!$categorie) {
            $this->setFlash('erreur', 'Catégorie introuvable.');
            redirectTo('categorie', 'index');
        }

        loadView('categories/form', [
            'title'   => 'Modifier la catégorie',
            'action'  => 'update',
            'donnees' => ['id' => $categorie->id, 'libelle' => $categorie->libelle],
            'errors'  => [],
        ]);
    }

    /**
     * Valide puis enregistre la modification.
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('categorie', 'index');
        }

        $id = (int) ($_POST['id'] ?? 0);

        $donnees = [
            'id'      => $id,
            'libelle' => trim($_POST['libelle'] ?? ''),
        ];

        $errors = validDataCategorie($donnees);

        // Le libellé peut rester le même pour cette catégorie, mais pas
        // correspondre à une AUTRE catégorie existante.
        if (!isset($errors['libelle'])) {
            $existe = $this->categorieModel->findByLibelle($donnees['libelle']);
            if ($existe && (int) $existe->id !== $id) {
                $errors['libelle'] = 'Cette catégorie existe déjà';
            }
        }

        if ($errors) {
            loadView('categories/form', [
                'title'   => 'Modifier la catégorie',
                'action'  => 'update',
                'donnees' => $donnees,
                'errors'  => $errors,
            ]);
            return;
        }

        $this->categorieModel->updateCategorie($id, $donnees);

        $this->setFlash('succes', "La catégorie {$donnees['libelle']} a été modifiée.");
        redirectTo('categorie', 'index');
    }

    /**
     * Supprime une catégorie (en POST uniquement).
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('categorie', 'index');
        }

        $id = (int) ($_POST['id'] ?? 0);

        $categorie = $id > 0 ? $this->categorieModel->findCategorie($id) : null;

        if (!$categorie) {
            $this->setFlash('erreur', 'Catégorie introuvable.');
            redirectTo('categorie', 'index');
        }

        $nbProduits = $this->categorieModel->countProduits($id);

        if ($nbProduits > 0) {
            $this->setFlash(
                'erreur',
                "Impossible de supprimer « {$categorie->libelle} » : "
                . "{$nbProduits} produit(s) y sont rattaché(s)."
            );
            redirectTo('categorie', 'index');
        }

        $this->categorieModel->deleteCategorie($id);

        $this->setFlash('succes', "La catégorie {$categorie->libelle} a été supprimée.");
        redirectTo('categorie', 'index');
    }

    // TODO (branche auth) : réserver ces écrans au gestionnaire avec
    // auth() puis hasRole(UtilisateurModel::ROLE_GESTIONNAIRE).

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