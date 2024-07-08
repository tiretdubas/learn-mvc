<?php

namespace MVC;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class Router
{
    /**
     * Cette propriété va contenir toutes les instances de Route qui sont dans le fichier routes.php
     */
    protected array $routes;

    /**
     * On récupère le service request qui a été injecté dans le constructeur depuis la méthode boot()
     */
    public function __construct(
        protected Request $request,
        protected Response $response,
        protected Twig $twig,
        protected Session $session
    ) {
        // On récupère les instances de Route et on les stocke dans la propriété $route
        $this->routes = require __DIR__ . '/../routes.php';
    }

    /**
     * Cette méthode est appelée depuis le front-controller (public/index.php) et est chargée de trouver l'action correspondant à la requête puis de l'exécuter
     */
    public function dispatch(): Response
    {
        // Méthode permettant de trouver l'action correspondant à la requête
        $route = $this->getMatchedRoute();

        // S'il n'y a pas de route correspondant à la requête, on est sur une bonne vieille erreur 404 !
        if (! $route) {
            throw new HttpException();
        }

        // Sinon, on exécute l'action correspondant à notre route
        $response = $this->makeResponse($route);

        // On retourne la réponse de l'action
        return $response;
    }

    /**
     * Logique pour récupérer la bonne route pour ma requête
     */
    public function getMatchedRoute(): Route|false
    {
        // On boucle sur toutes les routes tant que j'en n'ai pas trouvé une qui correspond à ma requête
        foreach ($this->routes as $route) {
            // Ici, on regarde si on n'a pas une valeur pour un champ _method dans l'éventuel formulaire qui vient d'être soumis. Si c'est le cas, on utilisera la méthode indiquée pour trouver notre route (pratique pour les méthodes HTTP DELETE, PATCH et PUT qu'on ne peut pas faire depuis le navigateur). Sinon, on récupère simplement la méthode de la requête
            $requestMethod = $this->request->request->get('_method') ?? $this->request->getMethod();

            // On compare la méthode HTTP de la requête avec la méthode de la route sur laquelle on itère actuellement
            $goodMethod = $requestMethod === $route->method;

            // On regarde si l'URI de la requête correspond à l'URI de la route sur laquelle on itère actuellement
            $goodURI = preg_match(
                $route->uri,
                rtrim($this->request->getPathInfo(), '/') ?: '/', // Cette logique permet de se débarasser d'un éventuel / que l'utilisateur pourrait avoir laissé à la fin de l'URI (ex: /hello/Steven/)
                $params
            );

            // Si la méthode ET l'URI correspondent, on a trouvé notre route !!!
            if ($goodMethod && $goodURI) {
                // La variable $params indiqué en 3ème argument de preg_match() au dessus récupère automatiquement tous les paramètres variables de notre URI ! Mais la première valeur va contenir toute l'URI qui ne nous intéresse pas. On vient donc la supprimer avec array_shift()
                array_shift($params);

                // On utilise le setter de la route qui a matché pour mettre de côté les valeurs des paramètres variables afin de les utiliser plus tard dans l'action de mon contrôleur
                $route->setParams($params);

                // On retourne la route (permet de sortir de la boucle par la même occasion)
                return $route;
            }
        }

        // Si aucune route ne correspond à la requête, on retourne false
        return false;
    }

    /**
     * Permet d'instancier le contrôleur qui correspond à la route qui a matchée
     */
    protected function makeResponse(Route $route): Response
    {
        [$controller, $method] = $route->action;

        // Nouvelle instance du contrôleur (correspond à new HelloController() si jamais $controller = HelloController::class)
        $controller = new $controller(
            $this->request,
            $this->response,
            $this->twig,
            $this->session
        );

        // On appelle ensuite l'action (correspond à $controller->hello('Steven') si jamais l'utilisateur à tenté d'accéder à l'URI /hello/Steven)
        $response = $controller->$method(...$route->getParams());

        return $response;
    }
}
