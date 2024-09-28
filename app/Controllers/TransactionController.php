<?php

declare(strict_types=1);

namespace App\Controllers;
use Slim\Views\Twig;
use App\ResponseFormatter;
use App\Services\RequestService;
use App\Services\CategoryService;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TransactionController
{
    public function __construct(
        private readonly Twig $twig, 
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly CategoryService $categoryService
    )
    {
    }

    public function index(Request $request, Response $response)
    {
        return $this->twig->render(
            $response,
            'transactions/index.twig',
            ['categories' => $this->categoryService->getCategoryName()] 
        );
    }

    
}
