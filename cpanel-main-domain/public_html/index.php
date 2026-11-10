<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$laravelPath = __DIR__.'/mamikhevan';

if (file_exists($maintenance = $laravelPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelPath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
