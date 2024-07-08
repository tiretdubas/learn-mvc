<?php

namespace MVC;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Session\Session;
use Valitron\Validator;

class Request extends SymfonyRequest
{
    public function validate(array $data, array $rules, Session $session): array|false
    {
        // Pour l'initialisation de Valitron, ça se passe ici : https://github.com/vlucas/valitron
        // On lui passe les données à valider
        $validator = new Validator($data, lang: 'fr');

        // On récupère les labels personnalisés pour avoir de beaux messages d'erreur
        $validator->labels(require_once __DIR__ . '/../resources/validation.php');

        // On fixe les règles de validation pour les valeurs dans $data
        $validator->mapFieldsRules($rules);

        // Si c'est validé, on retourne les données vérifiées ...
        if ($validator->validate()) {
            return $validator->data();
        }

        // ... sinon, on enregistre les erreurs dans une variable de session flash "errors" ...
        $session->getFlashBag()->set('errors', $validator->errors());

        // Avant d'enregistrer les données à remettre dans nos formulaires, on supprimera toujours le contenu des champs de mot de passe pour qu'ils soient complètement retapés par nos utilisateurs
        unset(
            $data['password'],
            $data['password_confirmation']
        );

        // ... et les valeurs des champs dans une variable de session flash "old" histoire de ne pas vider la formulaire de notre visiteur en cas d'erreur
        $session->getFlashBag()->set('old', $data);

        // Puis on retourne false pour symboliser cet échec cuisant
        return false;
    }
}
