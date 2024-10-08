<?php

declare(strict_types=1);

namespace App\Controllers;

use App\ResponseFormatter;
use App\Services\ReceiptService;
use League\Flysystem\Filesystem;
use App\Services\TransactionService;
use App\Contracts\RequestValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\RequestValidators\UploadReceiptRequestValidator;


class ReceiptController
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly TransactionService $transactionService,
        private readonly ReceiptService $receiptService,
        private readonly ResponseFormatter $responseFormatter,
        )
    {
    }

    public function store(Request $request, Response $response, array $args)
    {
        $file = $this->requestValidatorFactory->make(UploadReceiptRequestValidator::class)->validate(
            $request->getUploadedFiles()
        )['receipt'];
        $filename = $file->getClientFilename();

        $id = (int) $args['id'];

        if(! $id || ! ($transaction = $this->transactionService->getById($id)))
        {
            return $response->withStatus(404);
        }

        $randomFilename = bin2hex(random_bytes(25));

        $this->filesystem->write("receipts/$randomFilename", $file->getStream()->getContents());

        $this->receiptService->create($transaction, $filename,$randomFilename);

        return $this->responseFormatter->asJson( $response, $response);
    }
}
