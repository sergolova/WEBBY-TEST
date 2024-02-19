<?php

// REQUIREMENTS CHECK

if (!extension_loaded('mysqli')) {
    die ('The mysqli extension is not installed or activated');
}

if (!extension_loaded('mbstring')) {
    die ('The mbstring extension is not installed or activated');
}

// DEFINE GLOBAL CONSTANTS

const PROJECT_DIR = __DIR__;
const TEMPLATES_DIR = PROJECT_DIR . '/View/Templates';
const STYLES_DIR = PROJECT_DIR . '/View/Styles';
const CONTROLLER_DIR = PROJECT_DIR . '/Controller';
const CONFIG_DIR = PROJECT_DIR . '/Config';
define("STYLES_URL", 'http://' . $_SERVER['HTTP_HOST'] . '/View/Styles');