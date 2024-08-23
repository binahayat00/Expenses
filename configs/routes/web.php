<?php

declare(strict_types=1);

use Slim\App;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Controllers\HomeController;

return function (App $app) {
    $app->get('/', [HomeController::class,'index'])->add(AuthMiddleware::class);

    $app->get('/login', [AuthController::class,'loginView']);
    $app->get('/register', [AuthController::class,'registerView']);
    $app->post('/login', [AuthController::class,'logIn']);
    $app->post('/register', [AuthController::class,'register']);
    $app->post('/logout', [AuthController::class, 'logOut'])->add(AuthMiddleware::class);
};