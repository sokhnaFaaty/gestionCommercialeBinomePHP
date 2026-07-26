<?php
$ancienEmail = htmlspecialchars($donnees['email'] ?? '');
?>

<div class="mx-auto mt-10 max-w-sm">

    <h1 class="text-xl font-semibold tracking-tight text-slate-900">Connexion</h1>
    <p class="mt-1 text-sm text-slate-500">Accédez à votre espace.</p>

    <?php if ($flash): ?>
        <div class="mt-6 rounded-md border px-4 py-3 text-sm
                    <?= $flash['type'] === 'succes'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                            : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors['identifiants'])): ?>
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <?= htmlspecialchars($errors['identifiants']) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= path('auth','authenticate') ?>" class="mt-8 space-y-5">

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input type="email" id="email" name="email" value="<?= $ancienEmail ?>" autofocus
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
            <?php endif; ?>
        </div>

        <button type="submit"
                class="w-full rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Se connecter
        </button>

    </form>

</div>
