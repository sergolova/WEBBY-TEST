<?php

use Controller\AuthenticatedController;
use Controller\PublicController;

class Router {
    public function route($path, $authenticated = false) {
        $controller = $authenticated ? new AuthenticatedController() : new PublicController();

        switch ($path) {
            case '/':
                $controller->home();
                break;
            case '/about':
                $controller->about();
                break;
            case '/dashboard':
                if ($authenticated) {
                    $controller->dashboard();
                } else {
                    echo 'Доступ заборонено. Авторизуйтесь!';
                }
                break;
            default:
                echo '404 - Сторінка не знайдена';
        }
    }
}