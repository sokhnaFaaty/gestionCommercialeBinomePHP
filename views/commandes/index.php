<div class="flex items-baseline justify-between">
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Commandes</h1>
        <p class="mt-1 text-sm text-slate-500">
            <?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?> enregistrée<?= count($commandes) > 1 ? 's' : '' ?>
        </p>
    </div>

    <a href="<?= path('commande','create') ?>"
       class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
        Nouvelle commande
    </a>
</div>

<?php if ($flash): ?>
    <div class="mt-6 rounded-md border px-4 py-3 text-sm
                <?= $flash['type'] === 'succes'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                        : 'border-red-200 bg-red-50 text-red-800' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<?php if (!$commandes): ?>

    <p class="mt-10 rounded-md border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
        Aucune commande enregistrée pour le moment.
    </p>

<?php else: ?>

    <div class="mt-6 overflow-x-auto rounded-md border border-slate-200">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Numéro</th>
                    <th class="px-4 py-3 font-medium">Client</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Montant</th>
                    <th class="px-4 py-3 font-medium">Statut</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($commandes as $commande): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-500"><?= htmlspecialchars($commande->numero) ?></td>
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <?= htmlspecialchars($commande->client_prenom . ' ' . $commande->client_nom) ?>
                        </td>
                        <td class="px-4 py-3 tabular-nums"><?= htmlspecialchars($commande->date) ?></td>
                        <td class="px-4 py-3 tabular-nums"><?= formatPrix($commande->montant_total) ?></td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                         <?= $commande->validee
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'bg-amber-50 text-amber-700' ?>">
                                <?= $commande->validee ? 'Validée' : 'En attente' ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="<?= WEBROOT . 'commande/show/' . (int) $commande->id ?>"
                               class="mr-4 text-sm text-slate-500 hover:text-slate-900">
                                Détail
                            </a>
                            <a href="<?= WEBROOT . 'commande/edit/' . (int) $commande->id ?>"
                               class="mr-4 text-sm text-slate-500 hover:text-slate-900">
                                Modifier
                            </a>
                            <form method="post"
                                  action="<?= path('commande','delete') ?>"
                                  class="inline"
                                  onsubmit="return confirm('Supprimer définitivement cette commande ?');">
                                <input type="hidden" name="id" value="<?= (int) $commande->id ?>">
                                <button type="submit" class="text-sm text-slate-500 hover:text-red-600">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>