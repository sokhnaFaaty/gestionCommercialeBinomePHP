<!-- views/produits/index -->
<div class="flex items-baseline justify-between">
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Produits</h1>
        <p class="mt-1 text-sm text-slate-500">
            <?= count($produits) ?> produit<?= count($produits) > 1 ? 's' : '' ?>
            <?= $libelle !== '' ? 'trouvé' . (count($produits) > 1 ? 's' : '') . ' pour « ' . htmlspecialchars($libelle) . ' »' : 'enregistré' . (count($produits) > 1 ? 's' : '') ?>
        </p>
    </div>

    <a href="<?= path('produit','create') ?>"
       class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
        Ajouter un produit
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

<form method="get" action="<?= path('produit','index') ?>" class="mt-6 flex gap-2">
    <input type="text"
           name="libelle"
           value="<?= htmlspecialchars($libelle) ?>"
           placeholder="Rechercher par libellé"
           class="w-64 rounded-md border border-slate-300 px-3 py-2 text-sm placeholder:text-slate-400
                  focus:border-slate-900 focus:outline-none">

    <button type="submit"
            class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Rechercher
    </button>

    <?php if ($libelle !== ''): ?>
        <a href="<?= path('produit','index') ?>" class="self-center text-sm text-slate-500 hover:text-slate-900">
            Réinitialiser
        </a>
    <?php endif; ?>
</form>

<?php if (!$produits): ?>

    <p class="mt-10 rounded-md border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
        <?= $libelle !== ''
                ? 'Aucun produit ne correspond à cette recherche.'
                : 'Aucun produit enregistré pour le moment.' ?>
    </p>

<?php else: ?>

    <div class="mt-6 overflow-x-auto rounded-md border border-slate-200">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Référence</th>
                    <th class="px-4 py-3 font-medium">Libellé</th>
<th class="px-4 py-3 font-medium">Catégorie</th>
                    <th class="px-4 py-3 font-medium">Prix unitaire</th>
                    <th class="px-4 py-3 font-medium">Stock</th>
                    <th class="px-4 py-3 font-medium">Statut</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($produits as $produit): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-500"><?= htmlspecialchars($produit->reference) ?></td>
                        <td class="px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars($produit->libelle) ?></td>
<td class="px-4 py-3 text-slate-500"><?= htmlspecialchars($produit->categorie_libelle ?? '--') ?></td>
                        <td class="px-4 py-3 tabular-nums"><?= number_format((float) $produit->prix_unitaire, 2, ',', ' ') ?> €</td>
                        <td class="px-4 py-3 tabular-nums"><?= (int) $produit->qte_stock ?></td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                         <?= $produit->statut === 'disponible'
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'bg-red-50 text-red-700' ?>">
                                <?= htmlspecialchars($produit->statut) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="post" action="<?= path('produit','updateStock') ?>" class="flex items-center justify-end gap-2">
                                <input type="hidden" name="id" value="<?= (int) $produit->id ?>">
                                <input type="number" name="qte_stock" min="0" value="<?= (int) $produit->qte_stock ?>"
                                       class="w-20 rounded-md border border-slate-300 px-2 py-1 text-sm">
                                <button type="submit" class="text-sm text-slate-500 hover:text-slate-900">
                                    Mettre à jour
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>