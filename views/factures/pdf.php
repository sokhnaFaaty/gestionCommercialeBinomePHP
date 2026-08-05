<?php $resteDu = (float) $facture->montant - (float) $facture->montant_paye; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
    h1 { font-size: 20px; margin-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
    th { background: #f8fafc; }
    .text-right { text-align: right; }
    .total-row td { font-weight: bold; background: #f8fafc; }
    .infos td { border: none; padding: 2px 0; }
</style>
</head>
<body>

    <h1>Facture <?= htmlspecialchars($facture->numero) ?></h1>
    <p>Date : <?= htmlspecialchars($facture->date) ?></p>

    <table class="infos">
        <tr>
            <td><strong>Client :</strong> <?= htmlspecialchars($facture->client_prenom . ' ' . $facture->client_nom) ?></td>
            <td><strong>Commande :</strong> <?= htmlspecialchars($facture->commande_numero) ?></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Produit</th>
                <th class="text-right">Prix unitaire</th>
                <th class="text-right">Quantité</th>
                <th class="text-right">Sous-total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $ligne): ?>
                <tr>
                    <td><?= htmlspecialchars($ligne->produit_reference) ?></td>
                    <td><?= htmlspecialchars($ligne->produit_libelle) ?></td>
                    <td class="text-right"><?= formatPrix($ligne->prix_unitaire) ?></td>
                    <td class="text-right"><?= (int) $ligne->quantite ?></td>
                    <td class="text-right"><?= formatPrix((float) $ligne->prix_unitaire * (int) $ligne->quantite) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">Total</td>
                <td class="text-right"><?= formatPrix($facture->montant) ?></td>
            </tr>
        </tfoot>
    </table>

    <table style="margin-top: 24px;">
        <thead>
            <tr>
                <th>N° paiement</th>
                <th>Date</th>
                <th class="text-right">Montant versé</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$paiements): ?>
                <tr><td colspan="3">Aucun paiement reçu.</td></tr>
            <?php endif; ?>
            <?php foreach ($paiements as $paiement): ?>
                <tr>
                    <td><?= htmlspecialchars($paiement->numero) ?></td>
                    <td><?= htmlspecialchars($paiement->date) ?></td>
                    <td class="text-right"><?= formatPrix($paiement->montant_verse) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 16px;"><strong>Reste dû : <?= formatPrix($resteDu) ?></strong></p>
</body>
</html>
