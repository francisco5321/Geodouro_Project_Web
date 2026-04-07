<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . ($path === '/' ? '' : $path);

if ($path !== '/' && $path !== false && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
