<?php

namespace Controller;

use Model\Router;
use Model\UserManager as UserManager;
use \Exception as Exception;

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
        $authError = '';
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->csrfCheck();

                $username = @$_POST['username'];
                $password = @$_POST['password'];

                if (isset($_POST['login'])) {
                    $authResult = $this->userManager->login($username, $password);
                    if ($authResult) {
                        $this->router->redirectToName('home');
                    } else {
                        $authError = 'Invalid username or password';
                    }
                } elseif (isset($_POST['register'])) {
                    $authResult = $this->userManager->register($username, $password);
                    if ($authResult) {
                        $authResult = $this->userManager->login($username, $password);
                        if ($authResult) {
                            $this->router->redirectToName('home');
                        } else {
                            $authError = 'Invalid username or password';
                        }
                    } else {
                        $authError = 'Registration failed or user exists';
                    }
                }
            }
        } catch (Exception) {
            $authError = 'Registration failed! Internal error';
        } finally {
            $this->getTemplate('AuthTemplate', [
                'token' => $this->userManager->generateCsrfToken(),
                'noAuthInHeader' => true,
                'authError' => $authError,
                'styles' => ['main'],
            ]);
        }
    }

    /** Logout and redirect to Home
     * @return never
     */
    public function logout(): never
    {
        $this->userManager->logout();
        $this->router->redirectToName('login');
    }

    /** Deleting a user account and redirecting to the registration form
     * @return never
     */
    public function unregister(): never
    {
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