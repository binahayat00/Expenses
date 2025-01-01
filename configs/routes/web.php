<?php

declare(strict_types=1);

use Slim\App;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Middleware\GuestMiddleware;
use App\Controllers\VerifyController;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\ReceiptController;
use App\Controllers\CategoriesController;
use App\Middleware\VerifyEmailMiddleware;
use App\Controllers\TransactionController;
use App\Controllers\TransactionImporterController;

return function (App $app) {
    
    $app->group('', function(RouteCollectorProxy $group){

        $group->get('/', [HomeController::class, 'index']);

        $group->group('/categories', function (RouteCollectorProxy $categories) {
            $categories->get('', [CategoriesController::class, 'index']);
            $categories->get('/load', [CategoriesController::class, 'load']);
            $categories->post('', [CategoriesController::class, 'store']);
            $categories->delete('/{category}', [CategoriesController::class, 'delete']);
            $categories->get('/{category}', [CategoriesController::class, 'get']);
            $categories->post('/{category}', [CategoriesController::class, 'update']);
        });

        $group->group('/transactions', function(RouteCollectorProxy $transactions){
            $transactions->get('', [TransactionController::class, 'index']);
            $transactions->get('/load', [TransactionController::class, 'load']);
            $transactions->post('', [TransactionController::class, 'store']);
            $transactions->delete('/{transaction}', [TransactionController::class, 'delete']);
            $transactions->get('/{transaction}', [TransactionController::class, 'get']);
            $transactions->post('/{transaction}', [TransactionController::class, 'update']);
            $transactions->post('/{transaction}/receipts', [ReceiptController::class, 'store']);
            $transactions->get(
                '/{transaction}/receipts/{receipt}',
                [ReceiptController::class, 'download'],
            );
            $transactions->delete(
                '/{transaction}/receipts/{receipt}',
                [ReceiptController::class, 'delete']
            );
            // $transactions->post(
            //     '/import',
            //     [TransactionImporterController::class, 'import']
            // );

            $transactions->post('/{transaction}/review', [TransactionController::class, 'toggleReviewed']);

        });
    })->add(VerifyEmailMiddleware::class)->add(AuthMiddleware::class);

    $app->group('', function(RouteCollectorProxy $group){
        $group->post('/logout', [AuthController::class, 'logOut']);
        $group->get('/verify', [VerifyController::class, 'index']);
        $group->get('/verify/{id}/{hash}', [VerifyController::class, 'verify'])->setName('verify');
    })->add(AuthMiddleware::class);

    $app->group('', function (RouteCollectorProxy $guest) {
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->get('/register', [AuthController::class, 'registerView']);
        $guest->post('/login', [AuthController::class, 'logIn']);
        $guest->post('/register', [AuthController::class, 'register']);
    })->add(GuestMiddleware::class);
};