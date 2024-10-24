<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entity\Receipt;
use App\ResponseFormatter;
use App\Services\ReceiptService;
use League\Flysystem\Filesystem;
use App\Services\TransactionService;
use App\Contracts\RequestValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\RequestValidators\UploadReceiptRequestValidator;
use Slim\Psr7\Stream;


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

        $this->receiptService->create($transaction, $filename,$randomFilename, $file->getClientMediaType());

        return $this->responseFormatter->asJson( $response, $response);
    }

    public function download(Request $request, Response $response, array $args): Response
    {
        $transactionId = (int) $args['transactionId'];
        $receiptId = (int) $args['id'];

        if(! $transactionId || ! ($this->transactionService->getById($transactionId))){
            return $response->withStatus(404);
        }

        if(! $receiptId || ! ($receipt = $this->receiptService->getById((int) $receiptId))){
            return $response->withStatus(404);
        }

        if($receipt->getTransaction()->getId() !== $transactionId)
        {
            return $response->withStatus(401);
        }

        $file = $this->filesystem->readStream("receipts/" . $receipt->getStorageFilename());
        
        $response = $response->withHeader(
            'Content-Disposition',
            'inline; filename="' . $receipt->getFilename() . '"'
        )->withHeader('Content-Type',$receipt->getMediaType());

        return $response->withBody(new Stream($file));
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $transactionId = (int) $args['transactionId'];
        $receiptId = (int) $args['id'];

        if(! $transactionId || ! $this->transactionService->getById($transactionId))
        {
            return $response->withStatus(404);
        }

        if(! $receiptId || ! ($receipt = $this->receiptService->getById($receiptId)))
        {
            return $response->withStatus(404);
        }

        if($receipt->getTransaction()->getId() !== $transactionId)
        {
            return $response->withStatus(404);
        }

        $this->filesystem->delete('receipts/' . $receipt->getStorageFilename());

        $this->receiptService->delete($receipt);

        return $response;
    }
}
