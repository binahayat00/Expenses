<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;

class TransactionImporterController
{
    public function __construct(
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService $transactionService,
        private readonly CategoryService $categoryService,
    )
    {
    }

    public function import(Request $request, Response $response): Response
    {
        /**
          * UploadedFileInterface $file
         */
        $file = $this->requestValidatorFactory
            ->make(TransactionImportRequestValidator::class)
            ->validate($request->getUploadedFiles())['transaction'];
        
        $user = $request->getAttribute('user');
        $resource = fopen($file->getStream()->getMetaaata('uri'), 'r');

        fgetcsv($resource);

        while(($row = fgetcsv($resource)) !== false)
        {
            [$date, $description, $category, $amount] = $row;

            $date = new \DateTime($date);
            $category = $this->categoryService->findByName($category);
            $amount = str_replace(['$',','], '', $amount);

            
        }
        //TODO
    }
}
