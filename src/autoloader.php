<?php
spl_autoload_register(
    function ($class) {
        if (strpos($class, 'Cldr2Gettext\\') !== 0) {
            return;
        }
        $file = __DIR__.str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen('Cldr2Gettext'))).'.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
);
