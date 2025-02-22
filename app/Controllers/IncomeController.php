<?php

namespace App\Controllers;

use Slim\Views\Twig;
use App\Entity\Income;
use App\ResponseFormatter;
use App\Services\IncomeService;
use App\Services\RequestService;
use App\Contracts\EntityManagerServiceInterface;
use App\RequestValidators\IncomeRequestValidator;
use App\RequestValidators\RequestValidatorFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IncomeController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly IncomeService $incomeService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly RequestValidatorFactory $requestValidatorFactory,
        private readonly EntityManagerServiceInterface $entityManagerService
    )
    {
    }

    public function index(Response $response)
    {
        $incomes = $this->incomeService->getAll();

        return $this->twig->render(
            $response,
            'incomes/index.twig',
            ['incomes' => $incomes]
        );
    }

    public function store(Request $request, Response $response)
    {
        $data = $this->requestValidatorFactory->make(IncomeRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $income = $this->incomeService->create($data, $request->getAttribute('user'));

        $this->entityManagerService->sync($income);

        return $this->responseFormatter->asJson($response, $income);
    }

    public function load(Request $request, Response $response): Response
    {
        $params = $this->requestService->getDataTableQueryParameters($request);
        $incomes = $this->incomeService->getPaginatedTransactions($params);

        $transformer = function (Income $income) {
            return [
                'id' => $income->getId(),
                'source' => $income->getSource(),
                'amount' => $income->getAmount(),
                'date' => $income->getDate()->format('m/d/Y g:i A'),
            ];
        };

        $totalIncomes = count($incomes);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $incomes->getIterator()),
            $params->draw,
            $totalIncomes
        );
    }
}
