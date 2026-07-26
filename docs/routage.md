# Le routage de l'application

Ce document explique comment une adresse tapée dans le navigateur arrive
jusqu'au bon contrôleur, et comment ajouter une nouvelle page au projet.

---

## 1. Le trajet d'une requête

Quand on demande `http://localhost:8000/commande/show/7` :

```
navigateur
    |
    v
public/index.php      <- point d'entrée unique : charge Composer, le .env,
    |                    les constantes (ROOT, WEBROOT), les helpers,
    |                    démarre la session, puis appelle le routeur
    v
routes/web.php        <- LE ROUTEUR : cherche l'URL dans la table de routes
    |
    v
CommandeController::show(7)
    |
    v
CommandeModel         <- va chercher les données en base
    |
    v
loadView('commandes/show', [...])
    |
    v
views/layouts/base.layout.php  (+ partials/header.php et partials/footer.php)
```

Tout passe par `public/index.php` : c'est le seul fichier accessible depuis
le web. Les dossiers `app/`, `views/` et `routes/` ne sont jamais appelés
directement par le navigateur.

---

## 2. La table de routes

Elle se trouve dans la fonction `routes()`, en haut de
[`routes/web.php`](../routes/web.php). C'est **la liste de toutes les URL
autorisées du site**. Une adresse qui n'y figure pas renvoie une 404.

```php
'GET commande/show/{id}'  => [CommandeController::class, 'show'],
 ^^^  ^^^^^^^^^^^^^^^^^^      ^^^^^^^^^^^^^^^^^^^^^^^^^  ^^^^^^
 (1)         (2)                         (3)               (4)
```

1. **Le verbe HTTP.**
   - `GET` : afficher une page (un lien, la barre d'adresse).
   - `POST` : envoyer un formulaire (créer, modifier, supprimer).

   Déclarer une suppression en `POST` empêche qu'un simple lien — ou une
   image piégée sur un autre site — puisse déclencher l'action. Si on
   appelle une route `POST` en `GET`, le routeur répond **405**.

2. **Le chemin**, sans le `/` du début. `{id}` est un *paramètre* : il
   accepte n'importe quelle valeur, et cette valeur est transmise à la
   méthode. `/commande/show/7` appelle donc `show(7)`.

3. **La classe du contrôleur.** `::class` donne juste son nom complet
   (`App\Controllers\CommandeController`) sans charger le fichier : le
   contrôleur n'est chargé que si l'URL correspond vraiment.

4. **La méthode** à appeler dans cette classe.

### Deux raccourcis automatiques

Pour éviter de déclarer deux fois la même chose, le routeur complète l'URL
avant de chercher :

| URL tapée      | URL réellement cherchée |
| -------------- | ----------------------- |
| `/`            | `auth/login`            |
| `/commande`    | `commande/index`        |

### Route déclarée ≠ écran autorisé

Le routeur répond à une seule question : « cette URL existe-t-elle ? ». Il ne
décide pas *qui* a le droit de l'ouvrir. Ce contrôle-là se fait dans le
constructeur des contrôleurs, avec `authGestionnaire()` ou `authClient()` —
voir [`authentification.md`](authentification.md).

---

## 3. Comment le routeur compare (routes/web.php, étape 2)

L'URL et le chemin de chaque route sont découpés en **segments** (les
morceaux entre les `/`). `commande/show/7` en a trois.

Pour chaque ligne de la table :

1. si le nombre de segments diffère → ce n'est pas cette route, on passe ;
2. sinon on compare segment par segment :
   - un segment entre accolades (`{id}`) accepte tout, et sa valeur est
     mise de côté pour être passée au contrôleur ;
   - un segment fixe doit être identique, sinon la route est écartée ;
3. si le chemin correspond **et** que le verbe est le bon → c'est la route,
   on s'arrête.

Puis l'étape 3 répond :

| Situation                                          | Réponse |
| -------------------------------------------------- | ------- |
| Route trouvée                                       | la page |
| Chemin trouvé, mais avec un autre verbe             | **405** |
| Aucun chemin ne correspond                          | **404** |
| Route déclarée mais contrôleur/méthode inexistant   | **500** |

Le dernier cas est un filet de sécurité pendant le développement : il donne
un message clair (« route déclarée mais pas encore implémentée ») au lieu
d'une erreur PHP incompréhensible.

---

## 4. Ajouter une nouvelle page : la marche à suivre

Exemple — afficher la liste des produits.

**a.** Écrire la méthode dans le contrôleur :

```php
// app/Controllers/ProduitController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ProduitModel;

class ProduitController extends Controller
{
    private ProduitModel $produitModel;

    public function __construct()
    {
        $this->produitModel = new ProduitModel();
    }

    public function index(): void
    {
        loadView('produits/index', [
            'title'    => 'Produits',
            'produits' => $this->produitModel->allProduits(),
        ]);
    }
}
```

**b.** Ajouter le `use` en haut de `routes/web.php` :

```php
use App\Controllers\ProduitController;
```

**c.** Ajouter la ligne dans `routes()` :

```php
'GET produit/index' => [ProduitController::class, 'index'],
```

**d.** Créer la vue `views/produits/index.php`.

C'est tout : l'onglet « Produits » apparaît **tout seul** dans le menu
(voir la section 6).

> Sans l'étape (c), l'URL renvoie 404 même si le contrôleur existe. C'est
> voulu : rien n'est joignable tant que ce n'est pas déclaré.

---

## 5. Construire les liens : `path()` et `redirectTo()`

Ne jamais écrire une URL en dur dans une vue. Deux fonctions de
[`app/Helpers/helpers.php`](../app/Helpers/helpers.php) s'en chargent :

```php
// Dans une vue : un lien
<a href="<?= path('commande', 'index') ?>">Commandes</a>
// -> http://localhost:8000/commande/index

// Avec des paramètres de recherche (?telephone=77...)
<form method="get" action="<?= path('utilisateur', 'index') ?>">

// Dans un contrôleur : rediriger après un POST réussi
redirectTo('commande', 'index');
```

Pour une URL avec un paramètre dans le chemin (`{id}`), on la compose
avec `WEBROOT` :

```php
<a href="<?= WEBROOT . 'commande/show/' . (int) $commande->id ?>">Détail</a>
```

`WEBROOT` est défini dans `public/index.php` : si l'adresse du site change,
il n'y a qu'une seule ligne à modifier dans tout le projet.

---

## 6. Le menu de navigation

`views/partials/header.php` ne contient plus **que** du HTML : une balise
`<header>` et une boucle d'affichage. Toute la logique est passée dans deux
fonctions de `helpers.php` :

- **`navEntries()`** — renvoie les onglets à afficher. Un onglet n'apparaît
  que si sa route `GET xxx/index` est déclarée dans `routes()`. Les écrans
  pas encore écrits ne produisent donc aucun lien mort, et l'onglet
  apparaît de lui-même le jour où la route est ajoutée.
- **`currentController()`** — dit sur quelle rubrique on se trouve, pour
  souligner l'onglet actif. Elle lit la constante `URL_COURANTE`, définie
  par le routeur.

C'est la règle générale du MVC : **une vue affiche, elle ne décide pas**.
Elle ne lit ni `$_SERVER`, ni la base de données ; elle reçoit ce qu'elle
doit montrer.

---

## 7. Pourquoi une table plutôt que du routage automatique

La version précédente devinait la classe à partir de l'URL
(`commande` → `CommandeController`) et appelait la méthode portant le nom
du 2ᵉ segment. C'était pratique, mais :

| Problème                                                                                                                                          | Réglé par la table                                             |
| ------------------------------------------------------------------------------------------------------------------------------------------------ | -------------------------------------------------------------- |
| **Toute méthode `public` devenait une URL.** `/utilisateur/setFlash/erreur/coucou` appelait `setFlash()` si elle avait été `public` au lieu de `private`. | Seules les méthodes listées sont joignables. Le reste : 404.    |
| **Aucun contrôle du verbe HTTP.** Chaque action devait vérifier `REQUEST_METHOD` à la main.                                                        | Le verbe est déclaré dans la route, le routeur renvoie 405.      |
| **Aucune vue d'ensemble.** Il fallait ouvrir tous les contrôleurs pour connaître les URL du site.                                                  | Un seul tableau lisible en haut de `routes/web.php`.             |
| **URL collée au code.** Renommer une méthode changeait l'URL publique.                                                                             | L'URL et le nom de la méthode sont indépendants.                 |

Le prix à payer : une ligne à ajouter dans la table pour chaque nouvel
écran. C'est le fonctionnement de tous les vrais frameworks PHP (Symfony,
Laravel, Slim).

---

## 8. Récapitulatif des fichiers

| Fichier                        | Rôle                                                       |
| ------------------------------ | ---------------------------------------------------------- |
| `public/index.php`             | Point d'entrée : constantes, helpers, session, puis routeur |
| `routes/web.php`               | Table de routes + moteur de correspondance                  |
| `app/Helpers/helpers.php`      | `path()`, `redirectTo()`, `loadView()`, `navEntries()`, `currentController()` |
| `app/Controllers/`             | Reçoivent la requête, valident, appellent les modèles       |
| `app/Models/`                  | Requêtes SQL                                                |
| `views/`                       | Affichage uniquement                                        |
| `views/partials/header.php`    | Menu — HTML seul                                            |
