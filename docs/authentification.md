# L'authentification

Qui peut entrer dans l'application, et jusqu'où.

---

## 1. Deux rôles, deux applications

La table `utilisateur` contient les deux profils, distingués par sa colonne
`role` :

| Rôle           | Ce qu'il voit                                                   | Écran d'accueil     |
| -------------- | --------------------------------------------------------------- | ------------------- |
| `gestionnaire` | Clients, Produits, Commandes, Catégories — création, modification, suppression | `/utilisateur/index` |
| `client`       | Ses propres commandes, en lecture seule                          | `/espace/index`      |

Comptes de test (mot de passe `passer123` pour les deux) :

```
gestionnaire@exemple.sn    -> gestionnaire
sokhna.diop@exemple.sn     -> client
```

---

## 2. Le trajet d'une connexion

```
/  ou  /auth/login
    |
    v
AuthController::login()          affiche views/auth/login.php
    |
    v  (POST du formulaire)
AuthController::authenticate()
    |
    |-- champs vides ?          -> validDataLogin() renvoie les erreurs, on réaffiche
    |-- email inconnu ?          -> « Email ou mot de passe incorrect »
    |-- mot de passe faux ?      -> même message
    |
    v  tout est bon
session_regenerate_id(true)      nouvel identifiant de session
$_SESSION['user'] = [...]        id, nom, prenom, email, role
    |
    v
redirigerSelonRole()             gestionnaire -> /utilisateur/index
                                 client       -> /espace/index
```

Le message d'erreur est volontairement le même pour un email inconnu et pour
un mauvais mot de passe. Dire « cet email n'existe pas » reviendrait à
confirmer à un inconnu quels comptes existent.

`session_regenerate_id(true)` donne un nouvel identifiant de session au moment
de la connexion. Si quelqu'un avait réussi à imposer un identifiant de session
à la victime *avant* qu'elle se connecte, cet identifiant devient inutilisable
(attaque dite de « fixation de session »).

---

## 3. Protéger un écran : une seule ligne

Les contrôles sont dans le **constructeur**, pas dans chaque méthode. Le
routeur crée l'objet avant d'appeler l'action ([`routes/web.php`](../routes/web.php),
dernière ligne) : une seule ligne protège donc toutes les actions du contrôleur.

```php
class CommandeController extends Controller
{
    public function __construct()
    {
        authGestionnaire();   // <- protège index, show, create, store, edit, update, delete

        $this->commandeModel = new CommandeModel();
    }
```

Les deux barrières, dans [`app/Helpers/helpers.php`](../app/Helpers/helpers.php) :

| Fonction              | Effet                                                                        |
| --------------------- | ---------------------------------------------------------------------------- |
| `authGestionnaire()`  | Pas connecté → `/auth/login`. Connecté mais client → `/espace/index`.         |
| `authClient()`        | Pas connecté → `/auth/login`. Connecté mais gestionnaire → `/utilisateur/index`. |

Elles s'appuient sur trois fonctions plus simples, déjà présentes :
`isConnected()`, `auth()` et `hasRole()`.

**Un client qui tape `/commande/index` à la main n'est pas bloqué par une page
d'erreur : il est renvoyé chez lui.** C'est volontaire — il est déjà connecté,
le renvoyer vers la connexion n'aurait aucun sens.

---

## 4. Ne jamais faire confiance à l'URL

Dans l'espace client, la question « à qui appartient cette commande ? » ne se
règle **jamais** avec un identifiant venu de l'URL, mais toujours avec l'id
gardé en session :

```php
// EspaceClientController::show()
$client   = utilisateurConnecte();
$commande = $this->commandeModel->findCommande($id);

if (!$commande || (int) $commande->client_id !== (int) $client['id']) {
    $this->setFlash('erreur', 'Commande introuvable.');
    redirectTo('espace', 'index');
}
```

Sans la deuxième condition, il suffirait de changer le numéro dans
`/espace/show/7` pour lire les commandes des autres clients. Même principe
pour la liste : `allCommandesByClient()` filtre en SQL, avec
`WHERE c.client_id = ?`.

---

## 5. Le menu s'adapte au rôle

`navEntries()` filtre les onglets sur deux conditions : la route existe **et**
le rôle connecté a le droit de la voir. Le client ne voit donc que
« Mes commandes », le gestionnaire ses quatre onglets de gestion, et un
visiteur non connecté n'en voit aucun.

Le nom de la personne connectée et le bouton **Déconnexion** apparaissent à
droite du menu. La déconnexion est un formulaire `POST`, pas un lien : un
simple lien pourrait être déclenché à son insu (une image piégée sur une autre
page suffirait à déconnecter la personne).

---

## 6. Ce qui reste à faire

**Les mots de passe sont stockés en clair** dans la table `utilisateur`.
`authenticate()` compare donc les chaînes directement. C'est un choix
temporaire, assumé : quiconque lit la base lit tous les mots de passe.

Le passage au hachage tient en trois changements :

1. `AuthController::authenticate()` :
   `password_verify($donnees['password'], $utilisateur->password)`
2. `UtilisateurModel::createClient()` :
   `password_hash($data['password'], PASSWORD_DEFAULT)`
3. Convertir une fois les comptes déjà en base — sinon plus personne ne peut
   se connecter, `password_verify()` ne sachant pas lire un mot de passe en clair.

Le `TODO (sécurité)` est posé aux deux endroits concernés dans le code.

---

## 7. Récapitulatif des fichiers

| Fichier                                    | Rôle                                                 |
| ------------------------------------------ | ---------------------------------------------------- |
| `app/Controllers/AuthController.php`       | Connexion, déconnexion                                |
| `app/Controllers/EspaceClientController.php` | Espace du client : ses commandes, en lecture seule   |
| `views/auth/login.php`                     | Formulaire de connexion                               |
| `views/espace/index.php`                   | Liste des commandes du client connecté                |
| `app/Helpers/helpers.php`                  | `authGestionnaire()`, `authClient()`, `utilisateurConnecte()`, `hasRole()` |
| `app/Helpers/validate.php`                 | `validDataLogin()`                                    |
| `views/partials/header.php`                | Menu selon le rôle, identité, déconnexion             |
