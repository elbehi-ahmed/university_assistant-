<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context) : Kernel {
    $env = $context['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'dev';
    $debug = array_key_exists('APP_DEBUG', $context)
        ? (bool) $context['APP_DEBUG']
        : ((isset($_SERVER['APP_DEBUG']) ? (bool) $_SERVER['APP_DEBUG'] : ('dev' === $env)));

    return new Kernel($env, $debug);
};
