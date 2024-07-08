# Quelques ajouts qui font plaisir

## Les variables d'environnement

En environnement de dev local ou de prod, les identifiants pour se connecter à notre SGBD ne seront pas les mêmes ! Et ça sera le cas d'un paquet de valeurs dans nos applications qui varieront d'un environnement à l'autre.

On va donc vouloir créer des variables d'environnement afin de les changer de valeur aisément en fonction de celui-ci.

Tout ça est rendu facilement possible avec la dépendance `vlucas/phpdotenv` que j'ai installé sur notre framework !

Vos variables d'environnement se trouveront dans un fichier `.env` à la racine de votre projet (au format `CLE=VALEUR`).

Celles-ci seront ensuite facilement récupérable via la superglobale PHP `$_ENV`.

Ce fichier ne devra PAS être versioné puisqu'il contiendra souvent des données sensibles (identifiants, clés d'API etc...).

Je l'ai donc ajouté à notre fichier `.gitignore` et j'ai créé un fichier `.env.example` qui aura la même structure que le `.env` pour servir de modèle à chaque fois que quelqu'un fera un `git clone ...` du projet (mais ce fichier exemple n'aura pas les données sensibles).

J'ai ensuite utilisé des variables d'environnement pour les informations de connexion au SGBD dans le service `eloquent` et pour le service `exception_handler` (on en parle dans la section suivante) qui permet d'ajouter un gestionnaire d'exceptions personnalisé qui ne doit être créé qu'en environnement de production (donc avec `APP_ENV=prod`).

## Le gestionnaire d'exceptions

Le gestionnaire d'exceptions se trouve [juste ici](./src/ExceptionHandler.php).

Si jamais on est dans un environnement de production (donc un environnement où on souhaite afficher de belles pages d'erreurs à nos visiteurs et non des messages détaillés seulement utiles en développement), on va utiliser la fonction native de PHP `set_exception_handler()` qui permet d'indiquer la façon dont on souhaite gérer les erreurs qui n'ont pas été attrapées via un bloc `try ... catch`.

Je vous laisse lire les commentaires dans ce fichier pour en comprendre le fonctionnement.

## Les sessions

On l'a vu il y a quelques semaines, les sessions permettent de palier l'aspect sans état du protocole HTTP. Elles pourront nous permettre notamment d'authentifier un utilisateur ou bien d'enregistrer temporairement les erreurs dans un formulaires.

La dépendance `symfony/http-foundation` permet de gérer les sessions via un objet plutôt que par les fonctions que nous avions découvert en cours (`session_start()` par exemple).

J'ai donc créé un service `session` qui contient simplement une instance de la classe `Symfony\Component\HttpFoundation\Session\Session` de Symfony.

J'ai ensuite passé ce service `session` au service `router` qui l'a transmis au contrôleur instancié pour que celui-ci le stocke dans une propriété `$session`.

Nous aurons donc un accès simplifié à la session de notre visiteur depuis nos actions !

Au passage, vu qu'on aura aussi régulièrement besoin d'accéder aux données de notre requête depuis les contrôleurs, j'ai aussi injecté le service `request` dans le constructeur de notre contrôleur et je l'ai stocké dans une propriété `$request`.

Nos templates auront régulièrement besoin d'accéder à des données (notamment des variables de session) qu'on voudra se passer de transmettre à chaque fois via le tableau `$data` de la méthode `view()` prévu à cet effet.

C'est pourquoi j'ai créé quelques fonctions dans `app/functions.php` dont les valeurs de retour vont être (notamment) utilisées par des variables auxquelles tous nos templates auront accès par défaut. Vous pouvez voir la création de ces variables dans le [service Twig](./src/Twig.php) (on a également une fonction `method()` qui peut servir à générer un champ caché dans les formulaires pour matcher avec les routes `delete`, `put` et `patch`).

## Validation des données

Pour la validation des données, je me suis contenté d'ajouter une méthode `validate()` à notre classe `Request`.

Vu que ce service est maintenant accessible depuis nos contrôleurs, on pourra utiliser cette méthode dès qu'on devra traiter un formulaire.

Je vous invite à [consulter sa logique](./src/Request.php) pour bien la comprendre.

On devra donc transmettre un tableau de données à valider à la méthode `validate()` avec les règles de validation et la session dans laquelle enregistrer les données à afficher sur un formulaire mal rempli.

Cette méthode nous retournera :

- Un tableau des données validées si tout s'est bien passé
- Le booléen `false` s'il y a eu des erreurs dans le formulaire

Un exemple d'utilisation dans un contrôleur qui a tenu à garder l'anonymat :

```php
$data = [
    'name' => 'Steven Sil',
    'email' => 'foo@bar.baz',
]

$validated = $this->request->validate($data, [
    'name' => ['required', ['lengthMin', 5]],
    'email' => ['required', 'email'],
], $this->session);

// $validated === false si des données sont invalides
// $validated === [
//    'name' => 'Steven Sil',
//    'email' => 'foo@bar.baz',
// ] si les données sont correctes !
```
