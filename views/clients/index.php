<div class="flex items-baseline justify-between">
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Clients</h1>
        <p class="mt-1 text-sm text-slate-500">
            <?= count($clients) ?> client<?= count($clients) > 1 ? 's' : '' ?>
            <?= $telephone !== '' ? 'trouvé' . (count($clients) > 1 ? 's' : '') . ' pour « ' . htmlspecialchars($telephone) . ' »' : 'enregistré' . (count($clients) > 1 ? 's' : '') ?>
        </p>
    </div>

    <a href="<?= path('utilisateur','create') ?>"
       class="rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
        Ajouter un client
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

<form method="get" action="<?= path('utilisateur','index') ?>" class="mt-6 flex gap-2">
    <input type="text"
           name="telephone"
           value="<?= htmlspecialchars($telephone) ?>"
           placeholder="Rechercher par téléphone"
           class="w-64 rounded-md border border-slate-300 px-3 py-2 text-sm placeholder:text-slate-400
                  focus:border-slate-900 focus:outline-none">

    <button type="submit"
            class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Rechercher
    </button>

    <?php if ($telephone !== ''): ?>
        <a href="<?= path('utilisateur','index') ?>" class="self-center text-sm text-slate-500 hover:text-slate-900">
            Réinitialiser
        </a>
    <?php endif; ?>
</form>

<?php if (!$clients): ?>

    <p class="mt-10 rounded-md border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
        <?= $telephone !== ''
                ? 'Aucun client ne correspond à cette recherche.'
                : 'Aucun client enregistré pour le moment.' ?>
    </p>

<?php else: ?>

    <div class="mt-6 overflow-x-auto rounded-md border border-slate-200">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nom</th>
                    <th class="px-4 py-3 font-medium">Prénom</th>
                    <th class="px-4 py-3 font-medium">Téléphone</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($clients as $client): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900"><?= htmlspecialchars($client->nom) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($client->prenom) ?></td>
                        <td class="px-4 py-3 tabular-nums"><?= htmlspecialchars($client->telephone) ?></td>
                        <td class="px-4 py-3 text-slate-500"><?= htmlspecialchars($client->email) ?></td>
                        <td class="px-4 py-3 text-right">
                            <form method="post"
                                  action="<?= path('utilisateur','delete') ?>"
                                  onsubmit="return confirm('Supprimer définitivement ce client ?');">
                                <input type="hidden" name="id" value="<?= (int) $client->id ?>">
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
