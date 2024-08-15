<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use Slim\App;

return function (App $app) {
    return $app->get('/', [HomeController::class,'index']);
};