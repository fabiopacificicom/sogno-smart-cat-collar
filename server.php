<?php

/**
 * Router script for the PHP built-in web server (php -S).
 *
 * The built-in server 404s on any URL that maps to a non-existent physical
 * file — including Livewire's dynamic asset route (/livewire/livewire.js),
 * which is served by a Laravel route, not a real file. This script forwards
 * such requests to Laravel's front controller while still serving real
 * static assets (css/js/images in public/) directly.
 *
 * Usage: php -S 0.0.0.0:8001 -t public server.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');

// Serve real files directly (built assets, images, etc.).
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

// Everything else goes through Laravel.
require_once __DIR__.'/public/index.php';
