<?php

namespace MVC;

/**
 * Cette Exception nous permet de symbolyser les erreurs HTTP qu'on peut recontrer (code HTTP 4XX et 5XX).
 * Intérêt ? On traitera différemment ces erreurs quand on fera notre gestionnaire d'exceptions
 */
class HttpException extends \Exception
{
    /**
     * On utilise le code de l'exception pour y mettre le code de la réponse HTTP qu'on souhaitera renvoyer plus tard
     */
    public function __construct(string $message = 'Page non trouvée', int $code = 404)
    {
        // On fait juste appel au constructeur parent
        parent::__construct($message, $code);
    }
}
