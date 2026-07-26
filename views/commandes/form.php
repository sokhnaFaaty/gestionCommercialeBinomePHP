<?php
// Ce formulaire sert deux fois : à créer une commande et à en modifier une.
// C'est la variable $commande qui fait la différence : null = création.
$estModification = isset($commande) && $commande !== null;

$ancienTelephone     = htmlspecialchars($donnees['telephone'] ?? '');
$ancienneDescription = htmlspecialchars($donnees['description'] ?? '');

// Le panier de départ. Vide à la création, rempli en modification (ou après
// une erreur de validation). Il est transmis au JavaScript en JSON.
$lignesInitiales = $donnees['lignes'] ?? [];

// Erreurs renvoyées par le serveur. Le JavaScript en évite la plupart, mais
// elles restent la seule vraie garantie : voir CommandeController::store().
$messagesErreur = array_values($errors);
?>

<a href="<?= path('commande','index') ?>" class="text-sm text-slate-500 hover:text-slate-900">
    &larr; Retour à la liste
</a>

<h1 class="mt-4 text-xl font-semibold tracking-tight text-slate-900">
    <?= $estModification
            ? 'Modifier la commande ' . htmlspecialchars($commande->numero)
            : 'Nouvelle commande' ?>
</h1>

<?php if ($messagesErreur): ?>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-inside list-disc space-y-1">
            <?php foreach ($messagesErreur as $message): ?>
                <li><?= htmlspecialchars($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Les marges négatives font déborder le fond gris jusqu'aux bords de la
     zone de contenu, pour détacher les trois cartes blanches. -->
<div class="-mx-6 mt-6 bg-slate-100 px-6 py-8">
<form method="post"
      id="formulaire-commande"
      action="<?= $estModification ? path('commande','update') : path('commande','store') ?>"
      class="space-y-6">

    <?php if ($estModification): ?>
        <input type="hidden" name="id" value="<?= (int) $commande->id ?>">
    <?php endif; ?>

    <!-- ============================= 1. CLIENT ============================= -->
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-indigo-600">1. Client</h2>

        <div class="mt-5 grid gap-6 sm:grid-cols-3">
            <div>
                <label for="telephone" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                    Téléphone
                </label>
                <div class="mt-2 flex gap-2">
                    <input type="text" id="telephone" name="telephone" value="<?= $ancienTelephone ?>"
                           placeholder="Ex: 771234567"
                           class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm
                                  placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none">
                    <button type="button" id="chercher-client"
                            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white
                                   hover:bg-indigo-700">
                        OK
                    </button>
                </div>
                <p id="message-client" class="mt-1 text-sm"></p>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Nom</label>
                <input type="text" id="client-nom" readonly placeholder="Généré automatiquement"
                       class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm
                              text-slate-600 placeholder:text-slate-400">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Prénom</label>
                <input type="text" id="client-prenom" readonly placeholder="Généré automatiquement"
                       class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm
                              text-slate-600 placeholder:text-slate-400">
            </div>
        </div>
    </section>

    <!-- ============================ 2. PRODUIT ============================ -->
    <!-- Cette section reste inactive tant que le client n'est pas trouvé :
         une commande sans client n'a pas de sens, on impose donc l'ordre. -->
    <section id="section-produit" class="rounded-xl border border-slate-200 bg-slate-50 p-6 opacity-60">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-indigo-400">2. Produit</h2>

        <div class="mt-5">
            <label for="reference" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                Référence produit
            </label>
            <div class="mt-2 flex max-w-sm gap-2">
                <input type="text" id="reference" placeholder="Ex: REF-001" disabled
                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm
                              placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none
                              disabled:bg-slate-100">
                <button type="button" id="chercher-produit" disabled
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white
                               hover:bg-indigo-700 disabled:bg-indigo-300">
                    OK
                </button>
            </div>
            <p id="message-produit" class="mt-1 text-sm text-red-600"></p>
        </div>

        <div class="mt-5 grid gap-6 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Libellé</label>
                <input type="text" id="produit-libelle" readonly placeholder="Produit recherché"
                       class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm
                              text-slate-600 placeholder:text-slate-400">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Prix</label>
                <input type="text" id="produit-prix" readonly placeholder="0 F CFA"
                       class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm
                              text-slate-600 placeholder:text-slate-400">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Stock disponible
                </label>
                <div class="mt-2 flex items-center gap-2">
                    <input type="text" id="produit-stock" readonly placeholder="0"
                           class="w-24 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm
                                  text-slate-600 placeholder:text-slate-400">
                    <span id="produit-dispo"
                          class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-500">
                        Dispo : -
                    </span>
                </div>
            </div>
        </div>

        <!-- Apparaît seulement quand un produit a été trouvé : tant qu'il n'y a
             rien à ajouter, la quantité n'aurait aucun sens. -->
        <div id="bloc-quantite" class="mt-5 hidden items-end gap-3">
            <div>
                <label for="quantite" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Quantité
                </label>
                <input type="number" id="quantite" min="1" value="1"
                       class="mt-2 w-24 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm
                              focus:border-indigo-500 focus:outline-none">
            </div>
            <button type="button" id="ajouter-au-panier"
                    class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Ajouter au panier
            </button>
        </div>
    </section>

    <!-- =========================== 3. MON PANIER =========================== -->
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <h2 class="border-b border-slate-200 px-6 py-4 text-sm font-semibold uppercase tracking-wide text-indigo-600">
            3. Mon panier
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Référence</th>
                        <th class="px-6 py-3 font-semibold">Libellé</th>
                        <th class="px-6 py-3 font-semibold">Prix unit.</th>
                        <th class="px-6 py-3 font-semibold">Qté</th>
                        <th class="px-6 py-3 font-semibold">Total</th>
                        <th class="px-6 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <!-- Rempli par le JavaScript, à partir du tableau « panier ». -->
                <tbody id="corps-panier" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-end justify-between gap-6 border-t border-slate-200 px-6 py-5">
            <div class="w-full max-w-xs">
                <label for="description" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Description (optionnel)
                </label>
                <input type="text" id="description" name="description" value="<?= $ancienneDescription ?>"
                       placeholder="Ex: Commande urgente..."
                       class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm
                              placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none">
            </div>

            <div class="text-right">
                <p class="text-base font-medium text-slate-500">
                    Total : <span id="total-panier" class="text-xl font-bold text-indigo-600">0 F CFA</span>
                </p>
                <button type="submit" id="enregistrer" disabled
                        class="mt-3 rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white
                               hover:bg-indigo-700 disabled:bg-indigo-200">
                    <?= $estModification ? 'Enregistrer les modifications' : 'Enregistrer la commande' ?>
                </button>
            </div>
        </div>
    </section>

    <!-- Les lignes du panier sont recopiées ici en champs cachés au moment de
         l'envoi, sous la forme lignes[0][reference], lignes[0][quantite]...
         C'est exactement ce qu'attend CommandeController::store(). -->
    <div id="champs-caches"></div>

</form>
</div>

<script>
// =========================================================================
// Le panier vit en JavaScript : un tableau d'objets. Chaque modification
// redessine le tableau et recalcule le total, sans recharger la page.
// =========================================================================

let panier = <?= json_encode($lignesInitiales, JSON_UNESCAPED_UNICODE) ?>;

// Le produit affiché dans la section 2, en attente d'être ajouté au panier.
let produitCourant = null;

// Tant que le client n'est pas trouvé, la section 2 reste inactive.
let clientValide = false;

const $ = (id) => document.getElementById(id);

/** 5000 -> « 5 000 F CFA » (même format que formatPrix() côté PHP). */
function formatPrix(montant) {
    return new Intl.NumberFormat('fr-FR').format(montant) + ' F CFA';
}

// ------------------------------------------------------------------
// 1. Client : chercher le nom à partir du téléphone
// ------------------------------------------------------------------
$('chercher-client').addEventListener('click', async function () {
    const telephone = $('telephone').value.trim();

    oublierClient();

    if (telephone === '') {
        afficherMessageClient('Veuillez saisir un numéro', false);
        return;
    }

    const reponse = await fetch('<?= path('commande','chercherClient') ?>?telephone=' + encodeURIComponent(telephone));
    const client  = await reponse.json();

    if (!client.trouve) {
        // « Client introuvable » : la section produit reste fermée.
        afficherMessageClient(client.message, false);
        return;
    }

    $('client-nom').value    = client.nom;
    $('client-prenom').value = client.prenom;
    afficherMessageClient('Client trouvé', true);

    clientValide = true;
    ouvrirSectionProduit();
    majBoutonEnregistrer();
});

// Si le numéro est modifié après coup, le client affiché ne correspond plus :
// on referme tout, il faut revalider.
$('telephone').addEventListener('input', function () {
    if (clientValide) {
        oublierClient();
    }
});

function afficherMessageClient(message, succes) {
    const zone = $('message-client');
    zone.textContent = message;
    zone.className   = 'mt-1 text-sm ' + (succes ? 'text-emerald-600' : 'text-red-600');
}

/** Revenir à l'état de départ : aucun client validé, section produit fermée. */
function oublierClient() {
    clientValide = false;

    $('client-nom').value    = '';
    $('client-prenom').value = '';
    afficherMessageClient('', false);

    fermerSectionProduit();
    majBoutonEnregistrer();
}

function ouvrirSectionProduit() {
    $('section-produit').classList.remove('opacity-60');
    $('reference').disabled        = false;
    $('chercher-produit').disabled = false;
}

function fermerSectionProduit() {
    $('section-produit').classList.add('opacity-60');
    $('reference').disabled        = true;
    $('chercher-produit').disabled = true;
    $('reference').value = '';
    $('message-produit').textContent = '';
    reinitialiserProduit();
}

// ------------------------------------------------------------------
// 2. Produit : chercher le libellé, le prix et le stock
// ------------------------------------------------------------------
$('chercher-produit').addEventListener('click', async function () {
    const reference = $('reference').value.trim();

    $('message-produit').textContent = '';
    reinitialiserProduit();

    if (reference === '') {
        $('message-produit').textContent = 'Veuillez saisir une référence';
        return;
    }

    const reponse = await fetch('<?= path('commande','chercherProduit') ?>?reference=' + encodeURIComponent(reference));
    const produit = await reponse.json();

    if (!produit.trouve) {
        $('message-produit').textContent = produit.message;
        return;
    }

    produitCourant = produit;

    $('produit-libelle').value = produit.libelle;
    $('produit-prix').value    = formatPrix(produit.prix);
    $('produit-stock').value   = produit.stock;
    $('produit-dispo').textContent = 'Dispo : ' + stockRestant(produit.reference, produit.stock);

    // Le produit est identifié : la quantité a maintenant un sens.
    $('bloc-quantite').classList.remove('hidden');
    $('bloc-quantite').classList.add('flex');
    $('quantite').focus();
});

function reinitialiserProduit() {
    produitCourant = null;
    $('produit-libelle').value = '';
    $('produit-prix').value    = '';
    $('produit-stock').value   = '';
    $('produit-dispo').textContent = 'Dispo : -';
    $('quantite').value = 1;
    $('bloc-quantite').classList.add('hidden');
    $('bloc-quantite').classList.remove('flex');
}

/** Stock encore disponible, une fois retiré ce qui est déjà dans le panier. */
function stockRestant(reference, stock) {
    const dejaPris = panier
        .filter(ligne => ligne.reference === reference)
        .reduce((total, ligne) => total + Number(ligne.quantite), 0);

    return stock - dejaPris;
}

// ------------------------------------------------------------------
// 3. Panier : ajouter, retirer, totaliser
// ------------------------------------------------------------------
$('ajouter-au-panier').addEventListener('click', function () {
    if (!produitCourant) return;

    const quantite = parseInt($('quantite').value, 10);

    if (!quantite || quantite < 1) {
        $('message-produit').textContent = 'La quantité doit être supérieure à 0';
        return;
    }

    const restant = stockRestant(produitCourant.reference, produitCourant.stock);

    if (quantite > restant) {
        $('message-produit').textContent = 'Stock insuffisant : ' + restant + ' unité(s) disponible(s)';
        return;
    }

    // Même produit déjà au panier : on cumule au lieu d'ajouter une 2e ligne.
    const existante = panier.find(ligne => ligne.reference === produitCourant.reference);

    if (existante) {
        existante.quantite = Number(existante.quantite) + quantite;
    } else {
        panier.push({
            reference: produitCourant.reference,
            libelle:   produitCourant.libelle,
            prix:      produitCourant.prix,
            quantite:  quantite,
        });
    }

    $('reference').value = '';
    $('message-produit').textContent = '';
    reinitialiserProduit();
    afficherPanier();
    $('reference').focus();
});

function retirerDuPanier(index) {
    panier.splice(index, 1);
    afficherPanier();
}

function afficherPanier() {
    const corps = $('corps-panier');
    corps.innerHTML = '';

    if (panier.length === 0) {
        corps.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">
                    Le panier est vide. Ajoutez des produits ci-dessus.
                </td>
            </tr>`;
    }

    let total = 0;

    panier.forEach(function (ligne, index) {
        const sousTotal = Number(ligne.prix) * Number(ligne.quantite);
        total += sousTotal;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-6 py-4 font-mono text-xs text-slate-500"></td>
            <td class="px-6 py-4 font-medium text-slate-900"></td>
            <td class="px-6 py-4 tabular-nums">${formatPrix(ligne.prix)}</td>
            <td class="px-6 py-4 tabular-nums">${Number(ligne.quantite)}</td>
            <td class="px-6 py-4 tabular-nums">${formatPrix(sousTotal)}</td>
            <td class="px-6 py-4 text-right">
                <button type="button" class="text-sm text-slate-400 hover:text-red-600">Retirer</button>
            </td>`;

        // textContent (et pas innerHTML) pour les valeurs venant de la base :
        // un libellé contenant du HTML serait affiché tel quel, pas exécuté.
        tr.children[0].textContent = ligne.reference;
        tr.children[1].textContent = ligne.libelle;
        tr.querySelector('button').addEventListener('click', () => retirerDuPanier(index));

        corps.appendChild(tr);
    });

    $('total-panier').textContent = formatPrix(total);
    majBoutonEnregistrer();
}

/** On n'enregistre que si le client est validé ET le panier non vide. */
function majBoutonEnregistrer() {
    $('enregistrer').disabled = !clientValide || panier.length === 0;
}

// ------------------------------------------------------------------
// Envoi : recopier le panier en champs cachés
// ------------------------------------------------------------------
$('formulaire-commande').addEventListener('submit', function () {
    const conteneur = $('champs-caches');
    conteneur.innerHTML = '';

    panier.forEach(function (ligne, index) {
        conteneur.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="lignes[${index}][reference]" value="${ligne.reference}">
            <input type="hidden" name="lignes[${index}][quantite]"  value="${Number(ligne.quantite)}">
        `);
    });
});

// Premier affichage (panier vide, ou pré-rempli en modification).
afficherPanier();

// En modification, le nom du client est déjà connu : on l'affiche d'emblée.
<?php if ($ancienTelephone !== ''): ?>
    $('chercher-client').click();
<?php endif; ?>
</script>
