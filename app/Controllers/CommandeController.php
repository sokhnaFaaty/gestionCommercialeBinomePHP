<?php
use App\Models\ProduitCommandeModel;


public function show(int $id): void
{
    $commande = $this->commandeModel->findCommande($id);

    if (!$commande) {
        $this->setFlash('erreur', 'Commande introuvable.');
        redirectTo('commande', 'index');
    }

    $produitCommandeModel = new ProduitCommandeModel();

    loadView('commandes/show', [
        'title'    => 'Commande ' . $commande->numero,
        'commande' => $commande,
        'lignes'   => $produitCommandeModel->findByCommande($id),
    ]);
}