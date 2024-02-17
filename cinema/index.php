<?php

use Model\DatabaseManager;
use Model\Router;
use Model\UserManager;
use Model\MovieManager;

require "init.php";
require "autoloader.php";

// RESOURCE MANAGEMENT

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($uri !== '/' && file_exists(PROJECT_DIR . $uri)) {
    return false;
}

try {
    $router = Router::getRouter();
    $dbManager = DatabaseManager::getDatabaseManager();
    $userManager = UserManager::getUserManager();
    $movieManager = MovieManager::getMovieManager();
//    $m = $movieManager->getMovie(2);
//    var_dump($m); die;

//    $userManager->register('sera', 'qqq');
//    $userManager->login('sera', 'qqq');

//    DatabaseManager::openConnection();

//    if ($userManager->isLoggedIn()) {
//
//
//    //    DatabaseManager::closeConnection();
//    } else {
//
//    }
    $router->route($_SERVER['REQUEST_URI']);


} catch (Exception $e) {
    die($e->getMessage());
}