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

}