<?php

function isStaticResourceRequest(): bool
{
    $uri = urldecode(
        parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
    );

    return $uri !== '/' && file_exists(PROJECT_DIR . $uri);
}

// REQUIREMENTS CHECK

if (!extension_loaded('mysqli')) {
    die ('The mysqli extension is not installed or activated');
}

if (!extension_loaded('mbstring')) {
    die ('The mbstring extension is not installed or activated');
}

// DEFINE GLOBAL CONSTANTS

const PROJECT_DIR = __DIR__;
const TEMPLATES_DIR = PROJECT_DIR . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Templates';
const STYLES_DIR = PROJECT_DIR . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Styles';
const CONTROLLER_DIR = PROJECT_DIR . DIRECTORY_SEPARATOR . 'Controller';
const CONFIG_DIR = PROJECT_DIR . DIRECTORY_SEPARATOR . 'Config';
define("STYLES_URL", 'http://' . $_SERVER['HTTP_HOST'] . '/View/Styles');