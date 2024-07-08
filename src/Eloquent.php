<?php

namespace MVC;

use Illuminate\Database\Capsule\Manager;

class Eloquent extends Manager
{
    public function __construct()
    {
        parent::__construct();

        // On peut venir utiliser les variables d'environnement créées dans le .env avec la superglobale $_ENV de PHP
        $this->addConnection([
            'driver' => $_ENV['DB_CONNECTION'],
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'database' => $_ENV['DB_DATABASE'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
        ]);

        $this->bootEloquent();
    }
}
