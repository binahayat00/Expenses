<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\UploadedFileInterface;
use App\Services\TransactionImporterService;
use App\Contracts\RequestValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\RequestValidators\TransactionImportRequestValidator;

class TransactionImporterController
{
    public function __construct(
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionImporterService $transactionImporterService
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
        
        $this->transactionImporterService->importFromFile(
            $file->getStream()->getMetadata('uri'),
            $user
        );

        return $response;        
    }
}
