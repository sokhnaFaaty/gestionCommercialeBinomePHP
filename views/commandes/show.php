
<?php
// Cette vue sert au gestionnaire ET au client (espace client) : le lien de
// retour dépend donc de qui l'affiche. Par défaut, la liste du gestionnaire.
$retour = isset($retour) ? $retour : path('commande','index');

// L'espace client n'affiche pas de facture : il ne la passe pas, on considère
// alors qu'il n'y en a pas (le bloc facture est de toute façon réservé au
// gestionnaire, plus bas).
$facture = isset($facture) ? $facture : null;
?>
<a href="<?= $retour ?>" class="text-sm text-slate-500 hover:text-slate-900">
    &larr; Retour à la liste
</a>

<div class="mt-4 flex items-baseline justify-between">
    <h1 class="text-xl font-semibold tracking-tight text-slate-900">
        Commande <?= htmlspecialchars($commande->numero) ?>
    </h1>
    <span class="rounded-full px-3 py-1 text-xs font-medium
                 <?= $commande->validee ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?>">
        <?= $commande->validee ? 'Validée' : 'En attente' ?>
    </span>
</div>

<dl class="mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
    <div>
        <dt class="text-slate-500">Client</dt>
        <dd class="mt-1 font-medium text-slate-900">
            <?= htmlspecialchars($commande->client_prenom . ' ' . $commande->client_nom) ?>
        </dd>
    </div>
    <div>
        <dt class="text-slate-500">Téléphone</dt>
        <dd class="mt-1 font-medium text-slate-900"><?= htmlspecialchars($commande->client_telephone) ?></dd>
    </div>
    <div>
        <dt class="text-slate-500">Date</dt>
        <dd class="mt-1 font-medium text-slate-900"><?= htmlspecialchars($commande->date) ?></dd>
    </div>
    <?php if (!empty($commande->description)): ?>
        <div class="col-span-2 sm:col-span-3">
            <dt class="text-slate-500">Description</dt>
            <dd class="mt-1 text-slate-900"><?= htmlspecialchars($commande->description) ?></dd>
        </div>
    <?php endif; ?>
</dl>

<h2 class="mt-8 text-sm font-medium text-slate-700">Produits commandés</h2>

<div class="mt-3 overflow-x-auto rounded-md border border-slate-200">
    <table class="w-full text-sm">
        <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3 font-medium">Référence</th>
                <th class="px-4 py-3 font-medium">Produit</th>
                <th class="px-4 py-3 font-medium">Prix unitaire</th>
                <th class="px-4 py-3 font-medium">Quantité</th>
                <th class="px-4 py-3 font-medium text-right">Sous-total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($lignes as $ligne): ?>
                <tr>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500"><?= htmlspecialchars($ligne->produit_reference) ?></td>
                    <td class="px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars($ligne->produit_libelle) ?></td>
                    <td class="px-4 py-3 tabular-nums"><?= formatPrix($ligne->prix_unitaire) ?></td>
                    <td class="px-4 py-3 tabular-nums"><?= (int) $ligne->quantite ?></td>
                    <td class="px-4 py-3 text-right tabular-nums">
                        <?= formatPrix((float) $ligne->prix_unitaire * (int) $ligne->quantite) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="border-t border-slate-200 bg-slate-50">
                <td colspan="4" class="px-4 py-3 text-right font-medium text-slate-700">Total</td>
                <td class="px-4 py-3 text-right font-semibold text-slate-900">
                    <?= formatPrix($commande->montant_total) ?>
                </td>
            </tr>
        </tfoot>
    </table>
    <?php if (hasRole(ROLE_GESTIONNAIRE)): ?>
    <div class="mt-6">
        <?php if ($facture): ?>
            <a href="<?= WEBROOT . 'facture/show/' . (int) $facture->id ?>"
               class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Voir la facture
            </a>
        <?php else: ?>
            <form method="post" action="<?= path('facture','store') ?>">
                <input type="hidden" name="commande_id" value="<?= (int) $commande->id ?>">
                <button type="submit"
                        class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Générer la facture
                </button>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>