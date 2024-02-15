<?php

namespace Controller;

class CustomController
{
    const TEMPLATES_PATH = 'Template/';

    public function index()
    {
    }

    public function getTemplate(string $name, array $args=[])
    {
        extract($args);
        require self::TEMPLATES_PATH . $name . '.php';
    }


}