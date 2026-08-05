<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\PaiementModel;
use App\Models\FactureModel;

class PaiementController extends Controller
{
    private PaiementModel $paiementModel;
    private FactureModel  $factureModel;

    public function __construct()
    {
        authGestionnaire();

        $this->paiementModel = new PaiementModel();
        $this->factureModel  = new FactureModel();
    }

    /**
     * Cas d'utilisation « lister paiements ».
     */
    public function index(): void
    {
        loadView('paiements/index', [
            'title'     => 'Paiements',
            'paiements' => $this->paiementModel->allPaiements(),
            'flash'     => $this->getFlash(),
        ]);
    }

    /**
     * Formulaire d'enregistrement d'un paiement pour une facture donnée.
     */
    public function create(int $factureId): void
    {
        $facture = $this->factureModel->findFacture($factureId);

        if (!$facture) {
            $this->setFlash('erreur', 'Facture introuvable.');
            redirectTo('facture', 'index');
        }

        if ($facture->statut === 'totalement_payee') {
            $this->setFlash('erreur', 'Cette facture est déjà soldée.');
            header('Location: ' . WEBROOT . 'facture/show/' . $factureId);
            exit;
        }

        loadView('paiements/form', [
            'title'   => 'Enregistrer un paiement',
            'facture' => $facture,
            'donnees' => [],
            'errors'  => [],
        ]);
    }

    /**
     * Cas d'utilisation « enregistrer un paiement ».
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('facture', 'index');
        }

        $factureId = (int) ($_POST['facture_id'] ?? 0);
        $facture   = $factureId > 0 ? $this->factureModel->findFacture($factureId) : null;

        if (!$facture) {
            $this->setFlash('erreur', 'Facture introuvable.');
            redirectTo('facture', 'index');
        }

        $donnees = ['montant_verse' => trim($_POST['montant_verse'] ?? '')];
        $errors  = validDataPaiement($donnees);

        $resteDu = (float) $facture->montant - (float) $facture->montant_paye;

        if (!isset($errors['montant_verse']) && (float) $donnees['montant_verse'] > $resteDu) {
            $errors['montant_verse'] = 'Le montant dépasse le reste dû (' . formatPrix($resteDu) . ')';
        }

        if ($errors) {
            loadView('paiements/form', [
                'title'   => 'Enregistrer un paiement',
                'facture' => $facture,
                'donnees' => $donnees,
                'errors'  => $errors,
            ]);
            return;
        }

        $this->paiementModel->createPaiement($factureId, (float) $donnees['montant_verse']);

        $this->setFlash('succes', 'Le paiement a été enregistré.');
        header('Location: ' . WEBROOT . 'facture/show/' . $factureId);
        exit;
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