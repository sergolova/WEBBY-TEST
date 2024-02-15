<?php

require "install.php";
require "Router.php";
require "UserManager.php";
require "Controller/CustomController.php";
require "Controller/AuthenticatedController.php";
require "Controller/PublicController.php";
require "DatabaseManager.php";
require_once "Movie.php";

try {
    $router = new Router();
    $user = new UserManager();
    DatabaseManager::openConnection();

    $router->route($_SERVER['REQUEST_URI'], $user->isLoggedIn());

    if ($user->isLoggedIn()) {


    //    DatabaseManager::closeConnection();
    } else {
        
    }


} catch (Exception $e) {
    die($e->getMessage());
}