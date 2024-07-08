<?php

use App\Models\User;
use MVC\App;

if (! function_exists('method')) {
    /**
     * Servira à créer un champ caché dans les formulaires pour simuler une méthode HTTP autre que POST
     */
    function method(string $httpMethod): string
    {
        return '<input type="hidden" name="_method" value="' . $httpMethod . '">';
    }
}

if (! function_exists('errors')) {
    /**
     * Récupère le tableau des erreurs concernant nos formulaires
     */
    function errors(): array
    {
        $errors = App::getInstance()->make('session')->getFlashBag()->get('errors');
        return $errors;
    }
}

if (! function_exists('status')) {
    /**
     * Récupère le message de statut (ou null par défaut)
     */
    function status(): ?string
    {
        $status = App::getInstance()->make('session')->getFlashBag()->get('status');
        return $status[0] ?? null;
    }
}

if (! function_exists('old')) {
    /**
     * Un tableau des anciennes valeurs pour nos formulaires (pratique pour éviter de les re-remplir à chaque fois)
     */
    function old(): array
    {
        return App::getInstance()->make('session')->getFlashBag()->get('old');
    }
}

if (! function_exists('isAuth')) {
    /**
     * Permet de vérifier si un utilisateur est authentifié
     */
    function isAuth(): bool
    {
        return App::getInstance()->make('session')->has('user_id');
    }
}

if (! function_exists('isAdmin')) {
    /**
     * Permet de vérifier si un utilisateur est administrateur
     */
    function isAdmin(): bool
    {
        return ($user = User::find(App::getInstance()->make('session')->get('user_id'))) && $user->role === 'admin';
    }
}

if (! function_exists('isGuest')) {
    /**
     * Permet de vérifier si un utilisateur n'est pas authentifié
     */
    function isGuest(): bool
    {
        return ! App::getInstance()->make('session')->has('user_id');
    }
}
