<?php

namespace App\Controllers;

use App\ResponseFormatter;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use App\Config;

class HomeController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly TransactionService $transactionService,
        private readonly CategoryService $categoryService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly Config $config
    )
    {
    }

    public function index(Response $response): Response
    {
        $timezone = new \DateTimeZone($this->config->get('app_timezone'));
        $startDate             = \DateTime::createFromFormat('Y-m-d', date('Y-m-01'), $timezone);
        $endDate               = new \DateTime('now', $timezone);

        $totals                = $this->transactionService->getTotals($startDate, $endDate);
        $recentTransactions    = $this->transactionService->getRecentTransactions(10);
        $topSpendingCategories = $this->transactionService->getTopSpendingCategories(4);

        return $this->twig->render(
            $response,
            'dashboard.twig',
            [
                'totals'                => $totals,
                'transactions'          => $recentTransactions,
                'topSpendingCategories' => $topSpendingCategories,
            ]
        );
    }

    public function getYearToDateStatistics(Response $response): Response
    {
        $data = $this->transactionService->getMonthlySummary((int) date('Y'));

        return $this->responseFormatter->asJson($response, $data);
    }
}
