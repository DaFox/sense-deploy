<?php
# Setup application environment
define('APPLICATION_ROOT', realpath(__DIR__ . '/../..'));

# Load composer autoloader
if(($autoloader = realpath(APPLICATION_ROOT . '/vendor/autoload.php')) === false) {
    if(($autoloader = realpath(APPLICATION_ROOT . '/../../autoload.php')) === false) {
        throw new \Exception("Class auto-loader not found.");
    }
}

require_once $autoloader;