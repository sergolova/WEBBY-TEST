<?php

namespace Controller;

use JetBrains\PhpStorm\NoReturn;
use Model\MovieManager;
use Model\Router;
use Model\UserManager as UserManager;

class MainController extends CustomController
{
    public function home()
    {
        $userManager = UserManager::getUserManager();
        $movieManager = MovieManager::getMovieManager();

        $m = $movieManager->getMovie(2);

        $this->getTemplate('HomeTemplate', [
            'user' => $userManager->getCurrentUser(),
            'title' => 'sera!',
            'movies' => [$m, $m, $m],
        ]);
    }

    public function about(): void
    {
        echo 'Це сторінка "Про нас".';
    }

    public function login(): void
    {
        $userManager = UserManager::getUserManager();
        $router = Router::getRouter();
        $authError = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || ($_POST['csrf_token'] != $userManager->getCsrfToken())) {
                die('Invalid CSRF Token');
            }

            $username = @$_POST['username'];
            $password = @$_POST['password'];

            if (isset($_POST['login'])) {
                $authResult = $userManager->login($username, $password);
                if ($authResult) {
                    $router->redirect('/');
                } else {
                    $authError = 'Invalid username or password';
                }
            } elseif (isset($_POST['register'])) {
                $authResult = $userManager->register($username, $password);
                if ($authResult) {
                    $authResult = $userManager->login($username, $password);
                    if ($authResult) {
                        $router->redirect('/');
                    } else {
                        $authError = 'Invalid username or password';
                    }
                } else {
                    $authError = 'Registration failed or user exists';
                }
            }
        }

        $this->getTemplate('AuthTemplate', [
            'token' => $userManager->generateCsrfToken(),
            'noAuthInHeader' => true,
            'authError' => $authError,
        ]);
    }

    public function notFound(string $path): void
    {
        $this->getTemplate('NotFoundTemplate');
    }

    public function logout(): never
    {
        $userManager = UserManager::getUserManager();
        $router = Router::getRouter();
        $userManager->logout();
        $router->redirect('/login');
    }

    public function unregister(): never
    {
        $userManager = UserManager::getUserManager();
        $router = Router::getRouter();
        $user = $userManager->getCurrentUser();
        
        if ($user) {
            $userManager->logout();
            if ($userManager->unregister($user->username)) {
                $router->redirect('/login');
            }
        }
        die('Unregister user fail!');
    }
}