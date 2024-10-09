<?php

declare(strict_types=1);

namespace App\Controllers;

use League\Flysystem\Filesystem;
use Psr\Http\Message\UploadedFileInterface;
use App\Contracts\RequestValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\RequestValidators\UploadReceiptRequestValidator;


class ReceiptController
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory
        )
    {
    }

    public function store(Request $request, Response $response, array $args)
    {
        $file = $this->requestValidatorFactory->make(UploadReceiptRequestValidator::class)->validate(
            $request->getUploadedFiles()
        );
        $fileName = $file['receipt']->getClientFilename();

        $this->filesystem->write("receipts/$fileName", $file['receipt']->getStream()->getContents());

        return $response;
    }
}
