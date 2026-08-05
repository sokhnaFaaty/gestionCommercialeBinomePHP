<div class="flex items-baseline justify-between">
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Factures</h1>
        <p class="mt-1 text-sm text-slate-500">
            <?= count($factures) ?> facture<?= count($factures) > 1 ? 's' : '' ?>
        </p>
    </div>
</div>

<?php if ($flash): ?>
    <div class="mt-6 rounded-md border px-4 py-3 text-sm
                <?= $flash['type'] === 'succes'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                        : 'border-red-200 bg-red-50 text-red-800' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="mt-6 flex gap-2 border-b border-slate-200">
    <?php $onglets = ['toutes' => 'Toutes', 'impayees' => 'Impayées', 'soldees' => 'Soldées']; ?>
    <?php foreach ($onglets as $cle => $libelle): ?>
        <a href="<?= path('facture','index', $cle !== 'toutes' ? ['statut' => $cle] : []) ?>"
           class="border-b-2 px-3 py-2 text-sm
                  <?= $statut === $cle ? 'border-slate-900 text-slate-900 font-medium' : 'border-transparent text-slate-500 hover:text-slate-900' ?>">
            <?= $libelle ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (!$factures): ?>

    <p class="mt-10 rounded-md border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
        Aucune facture pour ce filtre.
    </p>

<?php else: ?>

    <div class="mt-6 overflow-x-auto rounded-md border border-slate-200">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Numéro</th>
                    <th class="px-4 py-3 font-medium">Commande</th>
                    <th class="px-4 py-3 font-medium">Client</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Montant</th>
                    <th class="px-4 py-3 font-medium">Statut</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($factures as $facture): ?>
                    <?php
                    $badges = [
                        'non payee'           => ['Impayée', 'bg-red-50 text-red-700'],
                        'partiellement_payee' => ['Partielle', 'bg-amber-50 text-amber-700'],
                        'totalement_payee'    => ['Soldée', 'bg-emerald-50 text-emerald-700'],
                    ];
                    [$libelleStatut, $classeStatut] = $badges[$facture->statut];
                    ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-500"><?= htmlspecialchars($facture->numero) ?></td>
                        <td class="px-4 py-3 text-slate-500"><?= htmlspecialchars($facture->commande_numero) ?></td>
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <?= htmlspecialchars($facture->client_prenom . ' ' . $facture->client_nom) ?>
                        </td>
                        <td class="px-4 py-3 tabular-nums"><?= htmlspecialchars($facture->date) ?></td>
                        <td class="px-4 py-3 tabular-nums"><?= formatPrix($facture->montant) ?></td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium <?= $classeStatut ?>">
                                <?= $libelleStatut ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="<?= WEBROOT . 'facture/show/' . (int) $facture->id ?>"
                               class="text-sm text-slate-500 hover:text-slate-900">
                                Détail
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>
