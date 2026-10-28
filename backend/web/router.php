<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = realpath(__DIR__ . $path);
$root = realpath(__DIR__);
if (PHP_SAPI === 'cli-server' && $file && $root && str_starts_with($file, $root) && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
