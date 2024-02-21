<?php
spl_autoload_register(function ($class) {
    $classFile = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($classFile)) {
        include $classFile;
    }
});