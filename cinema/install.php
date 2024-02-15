<?php

if (!extension_loaded('mysqli')) {
    die ('The mysqli extension is not installed or activated');
}

if (!extension_loaded('mbstring')) {
    die ('The mbstring extension is not installed or activated');
}