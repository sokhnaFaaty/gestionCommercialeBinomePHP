<?php
// Petit raccourci pour réafficher ce que l'utilisateur avait saisi.
$ancien = fn(string $champ) => htmlspecialchars($donnees[$champ] ?? '');
?>

<a href="<?= path('utilisateur','index') ?>" class="text-sm text-slate-500 hover:text-slate-900">
    &larr; Retour à la liste
</a>

<h1 class="mt-4 text-xl font-semibold tracking-tight text-slate-900">Nouveau client</h1>
<p class="mt-1 text-sm text-slate-500">Tous les champs sont obligatoires.</p>

<form method="post" action="<?= path('utilisateur','store') ?>" class="mt-8 max-w-lg space-y-5">

    <div>
        <label for="nom" class="block text-sm font-medium text-slate-700">Nom</label>
        <input type="text" id="nom" name="nom" value="<?= $ancien('nom') ?>"
               class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['nomVide']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['nomVide'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['nomVide']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="prenom" class="block text-sm font-medium text-slate-700">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="<?= $ancien('prenom') ?>"
               class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['prenomVide']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['prenomVide'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['prenomVide']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="telephone" class="block text-sm font-medium text-slate-700">Téléphone</label>
        <input type="text" id="telephone" name="telephone" value="<?= $ancien('telephone') ?>"
               class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['telephoneVide']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['telephoneVide'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['telephoneVide']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input type="text" id="email" name="email" value="<?= $ancien('email') ?>"
               class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['email']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['email'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['email']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Mot de passe</label>
        <input type="password" id="password" name="password"
               class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['password']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['password'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['password']) ?></p>
        <?php else: ?>
            <p class="mt-1 text-sm text-slate-500">6 caractères minimum. Il servira au client pour se connecter.</p>
        <?php endif; ?>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Enregistrer
        </button>
        <a href="<?= path('utilisateur','index') ?>" class="text-sm text-slate-500 hover:text-slate-900">
            Annuler
        </a>
    </div>

</form>
