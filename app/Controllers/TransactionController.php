<?php

declare(strict_types=1);

namespace App\Controllers;
use App\Contracts\EntityManagerServiceInterface;
use DateTime;
use Slim\Views\Twig;
use App\Entity\Receipt;
use App\ResponseFormatter;
use App\Entity\Transaction;
use App\Services\RequestService;
use App\Services\CategoryService;
use App\DataObjects\TransactionData;
use App\Services\TransactionService;
use Psr\Http\Message\ServerRequestInterface as Request;
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
        private readonly EntityManagerServiceInterface $entityManagerService
    ) {
    }

    public function index(Response $response)
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

        $transaction = $this->transactionService->create(
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new DateTime($data['date']),
                $data['category']
            ),
            $request->getAttribute('user')
        );

        $this->entityManagerService->sync($transaction);

        return $response;
    }

    public function delete(Response $response, Transaction $transaction): Response
    {
        $this->entityManagerService->delete($transaction, true);

        return $response;
    }

    public function get(Response $response, Transaction $transaction): Response
    {
        $data = [
            'id' => $transaction->getId(),
            'description' => $transaction->getDescription(),
            'amount' => $transaction->getAmount(),
            'date' => $transaction->getDate()->format('Y-m-d H:i:s'),
            'category' => $transaction->getCategory()->getId(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, Transaction $transaction): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $updated = $this->transactionService->update(
            $transaction,
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new DateTime($data['date']),
                $data['category']
            )
        );

        $this->entityManagerService->sync($updated);

        $this->entityManagerService->sync();

        return $this->responseFormatter->asJson($response,$updated);
    }

    public function load(Request $request, Response $response): Response
    {
        $params = $this->requestService->getDataTableQueryParameters($request);
        $transactions = $this->transactionService->getPaginatedTransactions($params);
        $transformer = function (Transaction $transaction) {
            return [
                'id' => $transaction->getId(),
                'description' => $transaction->getDescription(),
                'amount' => $transaction->getAmount(),
                'date' => $transaction->getDate()->format('m/d/Y g:i A'),
                'category' => $transaction->getCategory()?->getName(),
                'wasReviewed' => $transaction->getWasReviewed(),
                'receipts' => $transaction->getReceipts()->map(fn(Receipt $receipt) => [
                    'name' => $receipt->getFilename(),
                    'id' => $receipt->getId(),
                ])->toArray(),
            ];
        };

        $totalTransactions = count($transactions);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $transactions->getIterator()),
            $params->draw,
            $totalTransactions
        );
    }

    public function toggleReviewed(Response $response, Transaction $transaction): Response
    {
        $this->transactionService->toggleReviewed($transaction);
        $this->entityManagerService->sync();

        return $response;
    }
}
