<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\FactureModel;
use App\Models\CommandeModel;
use App\Models\PaiementModel;
use App\Models\ProduitCommandeModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class FactureController extends Controller
{
    private FactureModel $factureModel;
    private CommandeModel $commandeModel;
    private PaiementModel $paiementModel;

    public function __construct()
    {
        authGestionnaire();

        $this->factureModel  = new FactureModel();
        $this->commandeModel = new CommandeModel();
        $this->paiementModel = new PaiementModel();
    }

    /**
     * Cas d'utilisation « lister factures », avec les variantes
     * impayées / soldées / toutes (paramètre ?statut=...).
     */
    public function index(): void
    {
        $statut = $_GET['statut'] ?? 'toutes';

        $factures = match ($statut) {
            'impayees' => $this->factureModel->allFacturesByStatut('non payee'),
            'soldees'  => $this->factureModel->allFacturesByStatut('totalement_payee'),
            default    => $this->factureModel->allFactures(),
        };

        loadView('factures/index', [
            'title'    => 'Factures',
            'factures' => $factures,
            'statut'   => $statut,
            'flash'    => $this->getFlash(),
        ]);
    }

    /**
     * Détail d'une facture + ses paiements.
     */
    public function show(int $id): void
    {
        $facture = $this->factureModel->findFacture($id);

        if (!$facture) {
            $this->setFlash('erreur', 'Facture introuvable.');
            redirectTo('facture', 'index');
        }

        loadView('factures/show', [
            'title'     => 'Facture ' . $facture->numero,
            'facture'   => $facture,
            'paiements' => $this->paiementModel->findByFacture($id),
            'flash'     => $this->getFlash(),
        ]);
    }

    /**
     * Génère la facture d'une commande (bouton sur commande/show).
     * Le montant reprend toujours commande.montant_total.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectTo('commande', 'index');
        }

        $commandeId = (int) ($_POST['commande_id'] ?? 0);
        $commande   = $commandeId > 0 ? $this->commandeModel->findCommande($commandeId) : null;

        if (!$commande) {
            $this->setFlash('erreur', 'Commande introuvable.');
            redirectTo('commande', 'index');
        }

        // facture.commande_id est UNIQUE : une commande n'a jamais deux factures.
        if ($this->factureModel->findByCommande($commandeId)) {
            $this->setFlash('erreur', 'Cette commande a déjà une facture.');
            header('Location: ' . WEBROOT . 'commande/show/' . $commandeId);
            exit;
        }

        $factureId = $this->factureModel->createFacture($commandeId, (float) $commande->montant_total);

        $this->setFlash('succes', 'La facture a été générée.');
        header('Location: ' . WEBROOT . 'facture/show/' . $factureId);
        exit;
    }

    /**
     * Télécharge la facture au format PDF (« enregistrer sur l'ordinateur »).
     */
    public function pdf(int $id): void
    {
        $facture = $this->factureModel->findFacture($id);

        if (!$facture) {
            $this->setFlash('erreur', 'Facture introuvable.');
            redirectTo('facture', 'index');
        }

        $produitCommandeModel = new ProduitCommandeModel();

        $lignes    = $produitCommandeModel->findByCommande((int) $facture->commande_id);
        $paiements = $this->paiementModel->findByFacture($id);

        // Rendu à partir d'une vue HTML dédiée (pas le layout Tailwind : Dompdf
        // ne charge pas le CDN externe), capturée dans un buffer.
        ob_start();
        require ROOT . 'views/factures/pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream($facture->numero . '.pdf', ['Attachment' => true]);
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
