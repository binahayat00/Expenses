<?php

declare(strict_types=1);

namespace App\Controllers;
use DateTime;
use Slim\Views\Twig;
use App\Entity\Receipt;
use App\ResponseFormatter;
use App\Entity\Transaction;
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
    ) {
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

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->transactionService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $transaction = $this->transactionService->getById((int) $args['id']);

        if (!$transaction) {
            return $response->withStatus(404);
        }

        $data = [
            'id' => $transaction->getId(),
            'description' => $transaction->getDescription(),
            'amount' => $transaction->getAmount(),
            'date' => $transaction->getDate()->format('Y-m-d H:i:s'),
            'category' => $transaction->getCategory()->getId(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(TransactionRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $id = (int) $data['id'];

        if (!$id || !($transaction = $this->transactionService->getById($id))) {
            return $response->withStatus(404);
        }

        $result = $this->transactionService->update(
            $transaction,
            new TransactionData(
                $data['description'],
                (float) $data['amount'],
                new DateTime($data['date']),
                $data['category']
            )
        );

        return $this->responseFormatter->asJson($response,$result);
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
                'category' => $transaction->getCategory()->getName(),
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

    public function upload(Request $request, Response $response): Response
    {
        var_dump($request->getUploadedFiles());//['transaction']
        return $this->responseFormatter->asJson($response,$request);
    }
}
