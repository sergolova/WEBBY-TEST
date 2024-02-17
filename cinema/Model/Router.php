<?php

namespace Model;

use Controller\MainController;
use JetBrains\PhpStorm\NoReturn;

class Router
{
    public static ?Router $instance = null;

    public function route($path): void
    {
        $controller = new MainController();

        switch ($path) {
            case '/':
                $controller->home();
                break;
            case '/about':
                $controller->about();
                break;
            case '/login':
                $controller->login();
                break;
            case '/logout':
                $controller->logout();
                break;
            case '/unregister':
                $controller->unregister();
                break;
            default:
                $controller->notFound($path);
        }
    }

    public function redirect(string $to, int $code = 302): never
    {
        header("Location: $to", true, $code);
        exit;
    }

    public static function getRouter(): Router
    {
        if (self::$instance === null) {
            self::$instance = new Router();
        }
        return self::$instance;
    }
}