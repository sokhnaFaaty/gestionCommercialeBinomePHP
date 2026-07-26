<!-- views/produits/form.php -->
 
<?php
// Petit raccourci pour réafficher ce que l'utilisateur avait saisi.
$ancien = fn(string $champ) => htmlspecialchars($donnees[$champ] ?? '');
?>

<a href="<?= path('produit','index') ?>" class="text-sm text-slate-500 hover:text-slate-900">
    &larr; Retour à la liste
</a>

<h1 class="mt-4 text-xl font-semibold tracking-tight text-slate-900">Nouveau produit</h1>
<p class="mt-1 text-sm text-slate-500">Tous les champs sont obligatoires.</p>

<form method="post" action="<?= path('produit','store') ?>" class="mt-8 max-w-lg space-y-5">

    <div>
        <label for="reference" class="block text-sm font-medium text-slate-700">Référence</label>
        <input type="text" id="reference" name="reference" value="<?= $ancien('reference') ?>"
               class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['reference']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['reference'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['reference']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="libelle" class="block text-sm font-medium text-slate-700">Libellé</label>
        <input type="text" id="libelle" name="libelle" value="<?= $ancien('libelle') ?>"
               class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['libelle']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['libelle'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['libelle']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="prix" class="block text-sm font-medium text-slate-700">Prix unitaire</label>
        <input type="text" id="prix" name="prix" value="<?= $ancien('prix') ?>"
               class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['prix']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['prix'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['prix']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="quantite" class="block text-sm font-medium text-slate-700">Quantité en stock</label>
        <input type="text" id="quantite" name="quantite" value="<?= $ancien('quantite') ?>"
               class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['quantite']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['quantite'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['quantite']) ?></p>
        <?php endif; ?>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Enregistrer
        </button>
        <a href="<?= path('produit','index') ?>" class="text-sm text-slate-500 hover:text-slate-900">
            Annuler
        </a>
    </div>

</form>

