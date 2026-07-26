<!-- views/categories/index -->

<div class="flex items-baseline justify-between">
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Catégories</h1>
        <p class="mt-1 text-sm text-slate-500">
            <?= count($categories) ?> catégorie<?= count($categories) > 1 ? 's' : '' ?> enregistrée<?= count($categories) > 1 ? 's' : '' ?>
        </p>
    </div>

    <a href="<?= path('categorie','create') ?>"
       class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
        Ajouter une catégorie
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

<?php if (!$categories): ?>

    <p class="mt-10 rounded-md border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
        Aucune catégorie enregistrée pour le moment.
    </p>

<?php else: ?>

    <div class="mt-6 overflow-x-auto rounded-md border border-slate-200">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Libellé</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($categories as $categorie): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars($categorie->libelle) ?></td>
                        <td class="px-4 py-3 text-right">
                            <a href="<?= path('categorie','edit',['id' => $categorie->id]) ?>"
                               class="mr-4 text-sm text-slate-500 hover:text-slate-900">
                                Modifier
                            </a>
                            <form method="post"
                                  action="<?= path('categorie','delete') ?>"
                                  class="inline"
                                  onsubmit="return confirm('Supprimer définitivement cette catégorie ?');">
                                <input type="hidden" name="id" value="<?= (int) $categorie->id ?>">
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