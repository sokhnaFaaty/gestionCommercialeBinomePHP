<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\CommandeModel;
use App\Models\FactureModel;
use App\Models\ProduitCommandeModel;
use App\Models\ProduitModel;
use App\Models\UtilisateurModel;

/**
 * Gestion des commandes, réservée au gestionnaire.
 *
 * Une commande = un client + une ou plusieurs lignes (un produit et une
 * quantité). Le gestionnaire peut la consulter, la créer, la modifier et
 * la supprimer.
 */
class CommandeController extends Controller
{
    private CommandeModel $commandeModel;
    private ProduitCommandeModel $produitCommandeModel;
    private UtilisateurModel $utilisateurModel;
    private FactureModel $factureModel;

    public function __construct()
    {
        // Écran de gestion : réservé au gestionnaire connecté. Le routeur crée
        // l'objet avant d'appeler l'action, donc les 7 méthodes sont protégées
        // par cette seule ligne.
        authGestionnaire();

        $this->commandeModel        = new CommandeModel();
        $this->produitCommandeModel = new ProduitCommandeModel();
        $this->utilisateurModel     = new UtilisateurModel();
        $this->factureModel         = new FactureModel();
    }

    /**
     * Liste de toutes les commandes.
     */
    public function index(): void
    {
        loadView('commandes/index', [
            'title'     => 'Commandes',
            'commandes' => $this->commandeModel->allCommandes(),
            'flash'     => $this->getFlash(),
        ]);
    }

    /**
     * Détail d'une commande : ses informations et la liste de ses produits.
     */
    public function show(int $id): void
    {
        $commande = $this->commandeModel->findCommande($id);

        if (!$commande) {
            $this->setFlash('erreur', 'Commande introuvable.');
            redirectTo('commande', 'index');
        }

        loadView('commandes/show', [
            'title'    => 'Commande ' . $commande->numero,
            'commande' => $commande,
            'lignes'   => $this->produitCommandeModel->findByCommande($id),
            // null s'il n'y en a pas encore : la vue affiche alors le bouton
            // « Générer la facture » au lieu de « Voir la facture ».
            'facture'  => $this->factureModel->findByCommande($id) ?: null,
        ]);
    }

    /**
     * Affiche le formulaire de création (vide).
     */
    public function create(): void
    {
        loadView('commandes/form', [
            'title'    => 'Nouvelle commande',
            'commande' => null,   // null = création, voir views/commandes/form.php
            'donnees'  => [],
            'errors'   => [],
        ]);
    }

    /**
     * Valide puis enregistre une nouvelle commande.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('commande', 'create');
        }

        $donnees = [
            'telephone'   => trim($_POST['telephone'] ?? ''),
            'lignes'      => $_POST['lignes'] ?? [],
        ];

        // 1. Contrôles de forme (champs vides, quantités non numériques...).
        $errors = validDataCommande($donnees);

        // 2. Contrôles qui demandent la base : le client et les produits existent-ils ?
        //
        // Le formulaire vérifie déjà tout cela en JavaScript, mais on refait le
        // travail ici : le JavaScript peut être désactivé, ou la requête forgée
        // à la main. Un contrôle côté navigateur est un confort, jamais une
        // sécurité.
        $client         = $this->trouverClient($donnees['telephone'], $errors);
        $lignesValidees = $this->validerLignes($donnees['lignes'], $errors);

        if ($errors) {
            $donnees['lignes'] = $this->enrichirLignes($donnees['lignes']);

            loadView('commandes/form', [
                'title'    => 'Nouvelle commande',
                'commande' => null,
                'donnees'  => $donnees,
                'errors'   => $errors,
            ]);
            return;
        }

        $commandeId = $this->commandeModel->createCommande(
            (int) $client->id,
            $lignesValidees
        );
        $commande   = $this->commandeModel->findCommande($commandeId);

        $this->setFlash('succes', "La commande {$commande->numero} a été créée.");
        redirectTo('commande', 'index');
    }

    /**
     * Affiche le formulaire de modification, pré-rempli avec la commande.
     */
    public function edit(int $id): void
    {
        $commande = $this->commandeModel->findCommande($id);

        if (!$commande) {
            $this->setFlash('erreur', 'Commande introuvable.');
            redirectTo('commande', 'index');
        }

        if (!$this->estModifiable($commande)) {
            redirectTo('commande', 'index');
        }

        // Le panier du formulaire attend, pour chaque ligne, de quoi l'afficher
        // sans nouvelle requête : référence, libellé, prix et quantité.
        $lignes = [];

        foreach ($this->produitCommandeModel->findByCommande($id) as $ligne) {
            $lignes[] = [
                'reference' => $ligne->produit_reference,
                'libelle'   => $ligne->produit_libelle,
                'prix'      => (float) $ligne->prix_unitaire,
                'quantite'  => (int) $ligne->quantite,
            ];
        }

        loadView('commandes/form', [
            'title'    => 'Modifier la commande ' . $commande->numero,
            'commande' => $commande,
            'donnees'  => [
                'telephone'   => $commande->client_telephone,
                'lignes'      => $lignes,
            ],
            'errors'   => [],
        ]);
    }

    /**
     * Enregistre les modifications d'une commande existante.
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('commande', 'index');
        }

        $id       = (int) ($_POST['id'] ?? 0);
        $commande = $id > 0 ? $this->commandeModel->findCommande($id) : null;

        if (!$commande) {
            $this->setFlash('erreur', 'Commande introuvable.');
            redirectTo('commande', 'index');
        }

        if (!$this->estModifiable($commande)) {
            redirectTo('commande', 'index');
        }

        $donnees = [
            'telephone'   => trim($_POST['telephone'] ?? ''),
            'lignes'      => $_POST['lignes'] ?? [],
        ];

        $errors = validDataCommande($donnees);

        $client = $this->trouverClient($donnees['telephone'], $errors);

        // Les quantités déjà réservées par CETTE commande sont recréditées au
        // stock avant le contrôle : sans ça, renvoyer le formulaire sans rien
        // changer serait refusé pour « stock insuffisant ».
        $dejaReserve = [];

        foreach ($this->produitCommandeModel->findByCommande($id) as $ligne) {
            $reference = $ligne->produit_reference;

            $dejaReserve[$reference] = ($dejaReserve[$reference] ?? 0) + (int) $ligne->quantite;
        }

        $lignesValidees = $this->validerLignes($donnees['lignes'], $errors, $dejaReserve);

        if ($errors) {
            $donnees['lignes'] = $this->enrichirLignes($donnees['lignes']);

            loadView('commandes/form', [
                'title'    => 'Modifier la commande ' . $commande->numero,
                'commande' => $commande,
                'donnees'  => $donnees,
                'errors'   => $errors,
            ]);
            return;
        }

        $this->commandeModel->updateCommande(
            $id,
            (int) $client->id,
            $lignesValidees,
        );

        $this->setFlash('succes', "La commande {$commande->numero} a été modifiée.");
        redirectTo('commande', 'index');
    }

    /**
     * Supprime une commande (en POST uniquement, pour qu'un simple lien ne
     * puisse pas supprimer).
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('commande', 'index');
        }

        $id       = (int) ($_POST['id'] ?? 0);
        $commande = $id > 0 ? $this->commandeModel->findCommande($id) : null;

        if (!$commande) {
            $this->setFlash('erreur', 'Commande introuvable.');
            redirectTo('commande', 'index');
        }

        // facture.commande_id est en ON DELETE RESTRICT : on prévient avec un
        // message clair au lieu de laisser remonter une erreur SQL.
        if ($this->commandeModel->countFactures($id) > 0) {
            $this->setFlash(
                'erreur',
                "Impossible de supprimer la commande {$commande->numero} : une facture y est rattachée."
            );
            redirectTo('commande', 'index');
        }

        $this->commandeModel->deleteCommande($id);

        $this->setFlash('succes', "La commande {$commande->numero} a été supprimée.");
        redirectTo('commande', 'index');
    }

    // ---------------------------------------------------------------------
    // Recherches appelées par le JavaScript du formulaire
    // ---------------------------------------------------------------------
    // Ces deux méthodes ne renvoient pas une page HTML mais du JSON : c'est le
    // script de la page qui les interroge quand on clique sur « OK », et qui
    // remplit les champs avec la réponse. La page n'est jamais rechargée.

    /**
     * Cherche un client à partir de son numéro de téléphone.
     */
    public function chercherClient(): void
    {
        $telephone = trim($_GET['telephone'] ?? '');
        $client    = $telephone !== '' ? $this->utilisateurModel->findByTelephone($telephone) : null;

        if (!$client || $client->role !== UtilisateurModel::ROLE_CLIENT) {
            $this->repondreJson([
                'trouve'  => false,
                'message' => 'Client introuvable',
            ]);
        }

        $this->repondreJson([
            'trouve' => true,
            'nom'    => $client->nom,
            'prenom' => $client->prenom,
        ]);
    }

    /**
     * Cherche un produit à partir de sa référence.
     */
    public function chercherProduit(): void
    {
        $reference = trim($_GET['reference'] ?? '');
        $produit   = $reference !== '' ? (new ProduitModel())->findByReference($reference) : null;

        if (!$produit) {
            $this->repondreJson([
                'trouve'  => false,
                'message' => 'Produit introuvable',
            ]);
        }

        $this->repondreJson([
            'trouve'    => true,
            'reference' => $produit->reference,
            'libelle'   => $produit->libelle,
            'prix'      => (float) $produit->prix_unitaire,
            'stock'     => (int) $produit->qte_stock,
        ]);
    }

    // ---------------------------------------------------------------------
    // Méthodes internes (private = jamais joignables depuis une URL)
    // ---------------------------------------------------------------------

    /**
     * Envoie une réponse JSON et arrête là : pas de vue, pas de gabarit.
     */
    private function repondreJson(array $donnees): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Complète les lignes reçues du formulaire (référence + quantité) avec le
     * libellé et le prix, pour que le panier puisse être réaffiché tel quel
     * après une erreur de validation.
     *
     * Une référence inconnue est conservée : la personne doit revoir ce
     * qu'elle a saisi, pas le voir disparaître.
     */
    private function enrichirLignes(array $lignesPost): array
    {
        $produitModel = new ProduitModel();
        $lignes       = [];

        foreach ($lignesPost as $ligne) {
            $reference = trim($ligne['reference'] ?? '');
            $quantite  = (int) ($ligne['quantite'] ?? 0);

            if ($reference === '' && $quantite === 0) {
                continue;
            }

            $produit = $produitModel->findByReference($reference);

            $lignes[] = [
                'reference' => $reference,
                'libelle'   => $produit->libelle ?? '(référence inconnue)',
                'prix'      => (float) ($produit->prix_unitaire ?? 0),
                'quantite'  => $quantite,
            ];
        }

        return $lignes;
    }

    /**
     * Une commande facturée ne se modifie plus : la facture ne correspondrait
     * plus à son contenu. Pose le message d'erreur et renvoie false.
     */
    private function estModifiable(object $commande): bool
    {
        if ($this->commandeModel->countFactures((int) $commande->id) > 0) {
            $this->setFlash(
                'erreur',
                "Impossible de modifier la commande {$commande->numero} : une facture y est rattachée."
            );

            return false;
        }

        return true;
    }

    /**
     * Retrouve le client à partir de son téléphone.
     *
     * $errors est passé par référence (le & devant le paramètre) : la méthode
     * écrit directement dans le tableau du controller au lieu d'en recevoir
     * une copie.
     */
    private function trouverClient(string $telephone, array &$errors): ?object
    {
        if (isset($errors['telephone'])) {
            return null;   // champ vide : inutile d'interroger la base
        }

        $client = $this->utilisateurModel->findByTelephone($telephone);

        if (!$client) {
            $errors['telephone'] = "Aucun compte ne correspond à ce numéro";

            return null;
        }

        if ($client->role !== UtilisateurModel::ROLE_CLIENT) {
            $errors['telephone'] = "Ce numéro est celui d'un gestionnaire, pas d'un client";

            return null;
        }

        return $client;
    }

    /**
     * Vérifie chaque ligne du formulaire en base : le produit existe-t-il, et
     * le stock est-il suffisant ?
     *
     * @param array $lignesPost  Le contenu brut de $_POST['lignes']
     * @param array $errors      Tableau d'erreurs, complété au passage (&)
     * @param array $dejaReserve Quantités à recréditer au stock, par référence
     *                           (utilisé par update(), voir le commentaire là-bas)
     *
     * @return array Lignes prêtes pour le modèle : ['produit' => object, 'quantite' => int]
     */
    private function validerLignes(array $lignesPost, array &$errors, array $dejaReserve = []): array
    {
        // ProduitModel est instancié ici et pas dans le constructeur : tant que
        // la partie « produits » n'est pas terminée, les autres écrans des
        // commandes (liste, détail, suppression) restent utilisables.
        $produitModel = new ProduitModel();

        $lignesValidees  = [];
        $cumulParProduit = [];   // pour ne pas vendre deux fois le même stock

        foreach ($lignesPost as $i => $ligne) {
            $reference = trim($ligne['reference'] ?? '');
            $quantite  = (int) ($ligne['quantite'] ?? 0);

            // Ligne vide, ou déjà signalée par validDataCommande() : on passe.
            if ($reference === ''
                || isset($errors["ligne_{$i}_reference"])
                || isset($errors["ligne_{$i}_quantite"])) {
                continue;
            }

            $produit = $produitModel->findByReference($reference);

            if (!$produit) {
                $errors["ligne_{$i}_reference"] = "Aucun produit ne porte cette référence";
                continue;
            }

            // Stock réellement disponible pour cette ligne :
            //   ce qu'il reste en base
            // + ce que la commande réservait déjà (cas d'une modification)
            // - ce que les lignes précédentes du formulaire ont déjà pris
            $disponible = (int) $produit->qte_stock
                        + ($dejaReserve[$reference] ?? 0)
                        - ($cumulParProduit[$reference] ?? 0);

            if ($quantite > $disponible) {
                $errors["ligne_{$i}_quantite"] = "Stock insuffisant : {$disponible} unité(s) disponible(s)";
                continue;
            }

            $cumulParProduit[$reference] = ($cumulParProduit[$reference] ?? 0) + $quantite;

            $lignesValidees[] = [
                'produit'  => $produit,
                'quantite' => $quantite,
            ];
        }

        return $lignesValidees;
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