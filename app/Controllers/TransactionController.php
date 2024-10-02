<?php

declare(strict_types=1);

namespace App\Controllers;
use DateTime;
use Slim\Views\Twig;
use App\ResponseFormatter;
use App\Services\RequestService;
use App\Services\CategoryService;
use App\DataObjects\TransactionData;
use App\Services\TransactionService;
use Psr\Http\Message\RequestInterface as Request;
use App\RequestValidators\RequestValidatorFactory;
use Psr\Http\Message\ResponseInterface as Response;
use App\RequestValidators\TransactionRequestValidator;

class TransactionController
{
    public function __construct(
        private readonly Twig $twig, 
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly CategoryService $categoryService,
        private readonly TransactionService $transactionService,
        private readonly RequestValidatorFactory $requestValidatorFactory,
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

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->transactionService->create(
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new DateTime($data['date']),
                $data['category']
            ),
            $request->getAttribute('user')
        );

        return $response;
    }

    
}
