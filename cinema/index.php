<?php

use Model\Router;

require_once "init.php";
require_once "autoloader.php";

// RESOURCE MANAGEMENT

if (isStaticResourceRequest()) {
    return false;
}

// APPLICATION START

Router::getInstance()->route($_SERVER['REQUEST_URI']);