<?php

use Model\Router;

require "init.php";
require "autoloader.php";

$u = \Model\UserManager::getInstance();

//var_dump($u->getUserByName('gvs')); die;

// RESOURCE MANAGEMENT

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($uri !== '/' && file_exists(PROJECT_DIR . $uri)) {
    return false;
}

// APPLICATION START

Router::getInstance()->route($_SERVER['REQUEST_URI']);