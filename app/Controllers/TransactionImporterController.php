<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DataObjects\TransactionData;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Psr\Http\Message\UploadedFileInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\RequestValidators\TransactionImportRequestValidator;

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
        $resource = fopen($file->getStream()->getMetadata('uri'), 'r');

        fgetcsv($resource);

        while(($row = fgetcsv($resource)) !== false && $row !== '')
        {            
            [$date, $description, $category, $amount] = $row;
            if(
                strlen($date) === 0 && 
                strlen($description) === 0 &&
                strlen($category) === 0 &&
                strlen($amount) === 0
            )
            {
                break;
            }
            // var_dump($row);

            $date = new \DateTime($date);
            $category = $this->categoryService->findByName($category);
            $amount = str_replace(['$',','], '', $amount);

            $transactionData = new TransactionData($description, (float) $amount, $date, $category);

            $this->transactionService->create($transactionData, $user);

        }
        return $response;        
    }
}
