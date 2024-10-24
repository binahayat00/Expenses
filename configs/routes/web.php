<?php

declare(strict_types=1);

use Slim\App;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Middleware\GuestMiddleware;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\ReceiptController;
use App\Controllers\CategoriesController;
use App\Controllers\TransactionController;
use App\Controllers\TransactionImporterController;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index'])->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $guest) {
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->get('/register', [AuthController::class, 'registerView']);
        $guest->post('/login', [AuthController::class, 'logIn']);
        $guest->post('/register', [AuthController::class, 'register']);
    })->add(GuestMiddleware::class);

    $app->post('/logout', [AuthController::class, 'logOut'])->add(AuthMiddleware::class);

    $app->group('/categories', function (RouteCollectorProxy $categories) {
        $categories->get('', [CategoriesController::class, 'index']);
        $categories->get('/load', [CategoriesController::class, 'load']);
        $categories->post('', [CategoriesController::class, 'store']);
        $categories->delete('/{id:[0-9]+}', [CategoriesController::class, 'delete']);
        $categories->get('/{id:[0-9]+}', [CategoriesController::class, 'get']);
        $categories->post('/{id:[0-9]+}', [CategoriesController::class, 'update']);
    })->add(AuthMiddleware::class);

    $app->group('/transactions', function(RouteCollectorProxy $transactions){
        $transactions->get('', [TransactionController::class, 'index']);
        $transactions->get('/load', [TransactionController::class, 'load']);
        $transactions->post('', [TransactionController::class, 'store']);
        $transactions->delete('/{id:[0-9]+}', [TransactionController::class, 'delete']);
        $transactions->get('/{id:[0-9]+}', [TransactionController::class, 'get']);
        $transactions->post('/{id:[0-9]+}', [TransactionController::class, 'update']);
        $transactions->post('/{id:[0-9]+}/receipts', [ReceiptController::class, 'store']);
        $transactions->get(
            '/{transactionId:[0-9]+}/receipts/{id:[0-9]+}',
            [ReceiptController::class, 'download'],
        );
        $transactions->delete(
            '/{transactionId:[0-9]+}/receipts/{id:[0-9]+}',
            [ReceiptController::class, 'delete']
        );
        $transactions->post(
            '/import',
            [TransactionImporterController::class, 'import']
        );
    })->add(AuthMiddleware::class);
};