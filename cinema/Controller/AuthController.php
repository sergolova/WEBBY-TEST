<?php

namespace Controller;

use Model\Router;
use Exception;

class AuthController extends CommonController
{
    protected Router $router;

    public function __construct()
    {
        parent::__construct();
        $this->router = Router::getInstance();
    }

    /** POST
     * Route serving user authorization and registration.
     * After successful registration and authorization, redirect to Home
     * @return void
     */
    public function login(): void
    {
        $authErrors = [];
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->csrfCheck();

                $username = @$_POST['username'];
                $password = @$_POST['password'];

                $errors = $this->userManager->validateUserName($username) +
                    $this->userManager->validatePassword($password);

                if ($errors) {
                    $authErrors = array_merge($authErrors, $errors);
                } elseif (isset($_POST['login'])) {
                    $authResult = $this->userManager->login($username, $password);
                    if ($authResult) {
                        $this->router->redirectToName('home');
                    } else {
                        $authErrors[] = 'Invalid username or password';
                    }
                } elseif (isset($_POST['register'])) {
                    $authResult = $this->userManager->register($username, $password);
                    if ($authResult) {
                        $authResult = $this->userManager->login($username, $password);
                        if ($authResult) {
                            $this->router->redirectToName('home');
                        } else {
                            $authErrors[] = 'Invalid username or password';
                        }
                    } else {
                        $authErrors[] = 'Registration failed or user exists';
                    }
                }
            }
        } catch (Exception) {
            $authErrors[] = 'Registration failed! Internal error';
        } finally {
            $this->getTemplate('AuthTemplate', [
                'token' => $this->userManager->generateCsrfToken(),
                'noAuthInHeader' => true,
                'authErrors' => $authErrors,
                'styles' => ['main'],
            ]);
        }
    }

    /** Logout and redirect to Home
     * @return never
     */
    public function logout(): never
    {
        if (!$this->userManager->userCan('logout')) {
            $this->exitWithError('Access denied', 403);
        }

        $this->userManager->logout();
        $this->router->redirectToName('login');
    }

    /** Deleting a user account and redirecting to the registration form
     * @return never
     */
    public function unregister(): never
    {
        if (!$this->userManager->userCan('unregister')) {
            $this->exitWithError('Access denied', 403);
        }

        $user = $this->userManager->getCurrentUser();

        if ($user) {
            $this->userManager->logout();
            if ($this->userManager->unregister($user->id)) {
                $this->router->redirectToName('login');
            }
        }
        $this->exitWithError('Unregister user fail!', 500);
    }
}