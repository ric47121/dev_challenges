<?php
spl_autoload_register(function ($class) {

    $prefix = 'src/';
    $file = $prefix . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require $file;
    }

});