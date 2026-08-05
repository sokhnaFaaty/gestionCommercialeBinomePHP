<div class="flex items-baseline justify-between">
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Paiements</h1>
        <p class="mt-1 text-sm text-slate-500">
            <?= count($paiements) ?> paiement<?= count($paiements) > 1 ? 's' : '' ?> enregistré<?= count($paiements) > 1 ? 's' : '' ?>
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

<?php if (!$paiements): ?>

    <p class="mt-10 rounded-md border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
        Aucun paiement enregistré pour le moment.
    </p>

<?php else: ?>

    <div class="mt-6 overflow-x-auto rounded-md border border-slate-200">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Numéro</th>
                    <th class="px-4 py-3 font-medium">Facture</th>
                    <th class="px-4 py-3 font-medium">Client</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium text-right">Montant versé</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($paiements as $paiement): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-500"><?= htmlspecialchars($paiement->numero) ?></td>
                        <td class="px-4 py-3 text-slate-500">
                            <a href="<?= WEBROOT . 'facture/show/' . (int) $paiement->facture_id ?>" class="hover:text-slate-900">
                                <?= htmlspecialchars($paiement->facture_numero) ?>
                            </a>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <?= htmlspecialchars($paiement->client_prenom . ' ' . $paiement->client_nom) ?>
                        </td>
                        <td class="px-4 py-3 tabular-nums"><?= htmlspecialchars($paiement->date) ?></td>
                        <td class="px-4 py-3 text-right tabular-nums"><?= formatPrix($paiement->montant_verse) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<!-- SÉCURITÉ : On affiche le formulaire d'ajout uniquement si une facture est présente -->
<?php if (isset($facture) && is_object($facture)): ?>

    <?php $resteDu = (float) $facture->montant - (float) $facture->montant_paye; ?>

    <div class="mt-10 border-t border-slate-200 pt-6">
        <a href="<?= WEBROOT . 'facture/show/' . (int) $facture->id ?>" class="text-sm text-slate-500 hover:text-slate-900">
            &larr; Retour à la facture
        </a>

        <h1 class="mt-4 text-xl font-semibold tracking-tight text-slate-900">Enregistrer un paiement</h1>
        <p class="mt-1 text-sm text-slate-500">
            Facture <?= htmlspecialchars($facture->numero) ?> -- reste dû : <strong><?= formatPrix($resteDu) ?></strong>
        </p>

        <form method="post" action="<?= path('paiement','store') ?>" class="mt-8 max-w-sm space-y-5">

            <input type="hidden" name="facture_id" value="<?= (int) $facture->id ?>">

            <div>
                <label for="montant_verse" class="block text-sm font-medium text-slate-700">Montant versé</label>
                <input type="text" id="montant_verse" name="montant_verse"
                    value="<?= htmlspecialchars($donnees['montant_verse'] ?? '') ?>"
                    class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                            <?= isset($errors['montant_verse']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
                <?php if (isset($errors['montant_verse'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['montant_verse']) ?></p>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Enregistrer
                </button>
                <a href="<?= WEBROOT . 'facture/show/' . (int) $facture->id ?>" class="text-sm text-slate-500 hover:text-slate-900">
                    Annuler
                </a>
            </div>

        </form>
    </div>

<?php endif; ?>
