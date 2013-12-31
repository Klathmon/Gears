<?php

/**
 * Registers an autoloader for the package
 */
spl_autoload_register(function ($className) {
        $namespace = 'Gears\\';

        if (strpos($className, $namespace) === 0) {
            $className = str_replace($namespace, '', $className);
            $fileName = __DIR__ . DIRECTORY_SEPARATOR . 'Gears' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
            if (file_exists($fileName)) {
                require($fileName);
            }
        }
    });