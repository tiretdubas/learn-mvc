<?php

namespace MVC;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ExceptionHandler
{
    public function __construct(
        protected Response $response,
        protected Environment $twig,
    ) {
        // On créé un gestionnaire d'erreurs seulement si on est en prod ! Sinon, on laisse le comportement par défaut
        if ($_ENV['APP_ENV'] === 'prod') {
            set_exception_handler(function (\Throwable $t) {
                // Si l'exception lancée n'est pas de type HttpException, c'est que notre application a plantée quelque part d'imprévu ...
                if (! $t instanceof HttpException) {
                    // ... donc on va cache cette vilaine erreur pour en faire une HttpException avec un message généraliste
                    $t = new HttpException('Erreur serveur 🤖', 500);
                }

                // On indique le code HTTP de la réponse (404 par défaut avec HttpException)
                $this->response->setStatusCode($t->getCode());
                // On met en contenu la vue obtenue à partir du template d'erreur par défaut
                $this->response->setContent($this->twig->render('errors/default.html', [
                    'message' => $t->getMessage(),
                    'statusCode' => $t->getCode(),
                ]));
                // On envoie la réponse à notre visiteur
                $this->response->send();
            });
        }
    }
}
