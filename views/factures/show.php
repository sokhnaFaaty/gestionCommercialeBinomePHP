<a href="<?= path('facture','index') ?>" class="text-sm text-slate-500 hover:text-slate-900">
    &larr; Retour à la liste
</a>

<?php
$badges = [
    'non payee'           => ['Impayée', 'bg-red-50 text-red-700'],
    'partiellement_payee' => ['Partielle', 'bg-amber-50 text-amber-700'],
    'totalement_payee'    => ['Soldée', 'bg-emerald-50 text-emerald-700'],
];
[$libelleStatut, $classeStatut] = $badges[$facture->statut];
$resteDu = (float) $facture->montant - (float) $facture->montant_paye;
?>

<div class="mt-4 flex items-baseline justify-between">
    <h1 class="text-xl font-semibold tracking-tight text-slate-900">
        Facture <?= htmlspecialchars($facture->numero) ?>
    </h1>
    <span class="rounded-full px-3 py-1 text-xs font-medium <?= $classeStatut ?>">
        <?= $libelleStatut ?>
    </span>
</div>

<?php if ($flash): ?>
    <div class="mt-6 rounded-md border px-4 py-3 text-sm
                <?= $flash['type'] === 'succes'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                        : 'border-red-200 bg-red-50 text-red-800' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<dl class="mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
    <div>
        <dt class="text-slate-500">Client</dt>
        <dd class="mt-1 font-medium text-slate-900">
            <?= htmlspecialchars($facture->client_prenom . ' ' . $facture->client_nom) ?>
        </dd>
    </div>
    <div>
        <dt class="text-slate-500">Commande</dt>
        <dd class="mt-1 font-medium text-slate-900"><?= htmlspecialchars($facture->commande_numero) ?></dd>
    </div>
    <div>
        <dt class="text-slate-500">Date</dt>
        <dd class="mt-1 font-medium text-slate-900"><?= htmlspecialchars($facture->date) ?></dd>
    </div>
    <div>
        <dt class="text-slate-500">Montant total</dt>
        <dd class="mt-1 font-medium text-slate-900"><?= formatPrix($facture->montant) ?></dd>
    </div>
    <div>
        <dt class="text-slate-500">Déjà versé</dt>
        <dd class="mt-1 font-medium text-slate-900"><?= formatPrix($facture->montant_paye) ?></dd>
    </div>
    <div>
        <dt class="text-slate-500">Reste dû</dt>
        <dd class="mt-1 font-medium text-slate-900"><?= formatPrix($resteDu) ?></dd>
    </div>
</dl>

<div class="mt-6 flex gap-3">
    <a href="<?= WEBROOT . 'facture/pdf/' . (int) $facture->id ?>"
       class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Télécharger le PDF
    </a>
    <?php if ($facture->statut !== 'totalement_payee'): ?>
        <a href="<?= WEBROOT . 'paiement/create/' . (int) $facture->id ?>"
           class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Enregistrer un paiement
        </a>
    <?php endif; ?>
</div>

<h2 class="mt-8 text-sm font-medium text-slate-700">Paiements reçus</h2>

<?php if (!$paiements): ?>

    <p class="mt-3 rounded-md border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">
        Aucun paiement enregistré pour cette facture.
    </p>

<?php else: ?>

    <div class="mt-3 overflow-x-auto rounded-md border border-slate-200">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Numéro</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium text-right">Montant versé</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($paiements as $paiement): ?>
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500"><?= htmlspecialchars($paiement->numero) ?></td>
                        <td class="px-4 py-3 tabular-nums"><?= htmlspecialchars($paiement->date) ?></td>
                        <td class="px-4 py-3 text-right tabular-nums"><?= formatPrix($paiement->montant_verse) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>
