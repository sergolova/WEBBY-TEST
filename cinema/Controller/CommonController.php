<?php

namespace Controller;

class CustomController
{
    public function getTemplate(string $name, array $args = [])
    {
        $file = TEMPLATES_DIR . '/' . $name . '.php';
        if (file_exists($file)) {
            extract($args); // creating global variables for the template
            include $file;
        } else {
            echo ("Template not found: $file <br>\n");
        }
    }

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

        $this->getTemplate('ErrorTemplate', $args);
        exit;
    }

    public function notFound(string $path): void
    {
        $this->getTemplate('ErrorTemplate', [
            'styles' => ['main'],
        ]);
    }
}