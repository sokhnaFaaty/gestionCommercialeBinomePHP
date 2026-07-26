<?php
$ancienTelephone = htmlspecialchars($donnees['telephone'] ?? '');
$lignes = $donnees['lignes'] ?? [['reference' => '', 'quantite' => '']];
?>

<a href="<?= path('commande','index') ?>" class="text-sm text-slate-500 hover:text-slate-900">
    &larr; Retour à la liste
</a>

<h1 class="mt-4 text-xl font-semibold tracking-tight text-slate-900">Nouvelle commande</h1>

<form method="post" action="<?= path('commande','store') ?>" class="mt-8 max-w-2xl space-y-6">

    <div>
        <label for="telephone" class="block text-sm font-medium text-slate-700">Téléphone du client</label>
        <input type="text" id="telephone" name="telephone" value="<?= $ancienTelephone ?>"
               class="mt-1 w-full max-w-xs rounded-md border px-3 py-2 text-sm focus:outline-none
                      <?= isset($errors['telephone']) ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-slate-900' ?>">
        <?php if (isset($errors['telephone'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['telephone']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Produits</label>

        <?php if (isset($errors['lignes'])): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['lignes']) ?></p>
        <?php endif; ?>

        <div id="lignes-conteneur" class="mt-2 space-y-3">
            <?php foreach ($lignes as $i => $ligne): ?>
                <div class="ligne-produit flex items-start gap-3">
                    <div class="flex-1">
                        <input type="text" name="lignes[<?= $i ?>][reference]"
                               value="<?= htmlspecialchars($ligne['reference'] ?? '') ?>"
                               placeholder="Référence produit"
                               class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                                      <?= isset($errors["ligne_{$i}_reference"]) ? 'border-red-400' : 'border-slate-300 focus:border-slate-900' ?>">
                        <?php if (isset($errors["ligne_{$i}_reference"])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors["ligne_{$i}_reference"]) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="w-32">
                        <input type="number" min="1" name="lignes[<?= $i ?>][quantite]"
                               value="<?= htmlspecialchars($ligne['quantite'] ?? '') ?>"
                               placeholder="Quantité"
                               class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none
                                      <?= isset($errors["ligne_{$i}_quantite"]) ? 'border-red-400' : 'border-slate-300 focus:border-slate-900' ?>">
                        <?php if (isset($errors["ligne_{$i}_quantite"])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors["ligne_{$i}_quantite"]) ?></p>
                        <?php endif; ?>
                    </div>
                    <button type="button" onclick="this.closest('.ligne-produit').remove()"
                            class="mt-2 text-sm text-slate-400 hover:text-red-600">
                        &times;
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" id="ajouter-ligne"
                class="mt-3 text-sm font-medium text-slate-700 hover:text-slate-900">
            + Ajouter un produit
        </button>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Créer la commande
        </button>
        <a href="<?= path('commande','index') ?>" class="text-sm text-slate-500 hover:text-slate-900">
            Annuler
        </a>
    </div>

</form>

<script>
    // Index de départ pour les nouvelles lignes : après les lignes déjà affichées côté serveur.
    let indexLigne = <?= count($lignes) ?>;

    document.getElementById('ajouter-ligne').addEventListener('click', function () {
        const conteneur = document.getElementById('lignes-conteneur');

        const div = document.createElement('div');
        div.className = 'ligne-produit flex items-start gap-3';
        div.innerHTML = `
            <div class="flex-1">
                <input type="text" name="lignes[${indexLigne}][reference]" placeholder="Référence produit"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
            </div>
            <div class="w-32">
                <input type="number" min="1" name="lignes[${indexLigne}][quantite]" placeholder="Quantité"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
            </div>
            <button type="button" onclick="this.closest('.ligne-produit').remove()"
                    class="mt-2 text-sm text-slate-400 hover:text-red-600">
                &times;
            </button>
        `;

        conteneur.appendChild(div);
        indexLigne++;
    });
</script>