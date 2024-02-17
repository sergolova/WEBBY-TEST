<?php
spl_autoload_register(function ($class) {
    $classFile = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($classFile)) {
        include $classFile;
    }
});