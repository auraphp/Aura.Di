<?php
error_reporting(E_ALL);
$autoloader = __DIR__ . '/vendor/autoload.php';
if (! file_exists($autoloader)) {
    passthru('composer install');
}
require $autoloader;
