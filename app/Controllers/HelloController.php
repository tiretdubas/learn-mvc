<?php

namespace App\Controllers;

use App\Models\User;
use MVC\HttpException;
use Symfony\Component\HttpFoundation\Response;

class HelloController extends Controller
{
    public function index(): Response
    {
        return $this->response->setContent('Hello world');
    }

    public function hello(int $id): Response
    {
        // User est le point d'entrée vers Eloquent pour la table 'users'
        $user = User::find($id);

        // Si aucune entrée n'est trouvée, $user === null donc on lance une HttpException
        if (! $user) {
            throw new HttpException();
        }

        return $this->view('hello.html', [
            'name' => $user->name, // Chaque champ dans ma table est disponible en tant que propriété des instances qui représentent les entrées
        ]);
    }
}
