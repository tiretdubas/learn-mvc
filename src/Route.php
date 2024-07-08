<?php

namespace MVC;

use App\Controllers\Controller;

class Route
{
    // On stocke les méthodes HTTP possible dans une constante
    protected const array HTTP_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    // Servira à stocker les valeurs pour les paramètres variables de la route si elle matche avec la requête (Ex: Pour la route '/hello/{name}', on aura $params = ['Steven'] si l'utilisateur à indiqué /hello/Steven lors de sa requête)
    protected array $params;

    // On utilise le constructor property promotion pour créer nos propriétés
    protected function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array $action,
    ) {}

    // Chaque appel de méthode statique (Route::get()) atterrira ici
    public static function __callStatic(string $httpMethod, array $uriAndAction): Route
    {
        // On commence par vérifier si la méthode HTTP de la méthode statique est valide
        if (! in_array(
            $httpMethod = strtoupper($httpMethod),
            static::HTTP_METHODS
        )) {
            throw new \InvalidArgumentException('Méthode HTTP ' . $httpMethod . ' erronée');
        }

        // On vérifie que la méthode statique avait bien 2 arguments
        if (count($uriAndAction) !== 2) {
            throw new \InvalidArgumentException('Vous devez seulement indiquer 2 arguments pour vos routes');
        }

        // On utilise la décomposition pour stocker l'URI et l'action dans 2 variables
        [$uri, $action] = $uriAndAction;
        // Equivalent à
        // $uri = $uriAndAction[0];
        // $action = $uriAndAction[1];

        // On vérifie si l'URI est bien une chaîne de caractères (ex: '/hello/{name}')
        if (! is_string($uri)) {
            throw new \InvalidArgumentException('L\'URI doit être une chaîne de caractères');
        }

        // On vérifie si l'action est bien un tableau de 2 éléments avec une classe enfant de Controller en premier et une méthode de cette classe en deuxième
        if (count($action) !== 2 || ! is_subclass_of($action[0], Controller::class) || ! method_exists($action[0], $action[1])) {
            throw new \InvalidArgumentException('L\'action doit être composée d\'une classe contrôleur et d\'un nom de méthode existante');
        }

        // On convertit l'URI en regex
        $uri = static::convertToRegex($uri);

        // Tout est prêt, on retourne l'instance de notre nouvelle Route
        return new static(
            method: $httpMethod,
            uri: $uri,
            action: $action,
        );
    }

    public static function convertToRegex(string $uri): string
    {
        // Toutes les modifications à faire sur nos URI pour les transformer en regex valides
        // Voir ce point : https://github.com/DWWM-AL/CCP2-jour21-framework-mvc-atelier-3?tab=readme-ov-file#des-param%C3%A8tres-variables-dans-nos-routes
        $regex = '/^' . preg_replace([
            '/\//',
            '/{[\w-]+}/',
        ], [
            '\/',
            '([\w-]+)',
        ], $uri) . '$/';

        return $regex;
    }

    /**
     * Un setter pour la propriété protégée $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Un getter pour la propriété protégée $params
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
