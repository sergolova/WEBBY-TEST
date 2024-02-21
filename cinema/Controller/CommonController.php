<?php

namespace Controller;

use Model\UserManager;

class CommonController
{
    protected UserManager $userManager;

    public function __construct()
    {
        $this->userManager = UserManager::getInstance();
    }

    /** Checks the CSRF token and, if there is a mismatch, throws a 405 error
     * @return void
     */
    public function csrfCheck(): void
    {
        if (!isset($_POST['csrf_token']) || ($_POST['csrf_token'] !== $this->userManager->getCsrfToken())) {
            $this->exitWithError('Invalid CSRF Token', 405);
        }
    }

    /**
     * @param string $name -template name relative to templates folder
     * @param array $args - variables that will be available in the template
     * @return void
     */
    public function getTemplate(string $name, array $args = []): void
    {
        $file = TEMPLATES_DIR . DIRECTORY_SEPARATOR . $name . '.php';
        if (file_exists($file)) {
            extract($args); // creating global variables for the template
            include $file;
        } else {
            echo("Template not found: $file <br>\n");
        }
    }

    /** Shows an error in the error template
     * @param string $message
     * @param int $code - HTTP response code
     * @return never
     */
    public function exitWithError(string $message = '', int $code = 0): never
    {
        $args = [
            'styles' => ['main']
        ];

        if ($message) {
            $args['message'] = $message;
        }
        if ($code) {
            $args['code'] = $code;
        }

        http_response_code($code);
        $this->getTemplate('ErrorTemplate', $args);
        exit;
    }

    public function notFound(): never
    {
        $this->exitWithError('Not found :(',404);
    }
}