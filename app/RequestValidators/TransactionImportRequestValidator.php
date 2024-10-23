<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

class TransactionImportRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $uploadedFile = $data['transaction'] ?? null;

        if(! $uploadedFile)
        {
            throw new ValidationException(['transaction' => ['Please select a file to import!']]);
        }

        if($uploadedFile->getError() !== UPLOAD_ERR_OK)
        {
            throw new ValidationException(['transaction' => ['Failed to upload the file for import!']]);
        }

        $maxFileSize = 20 * 1024 * 1024;

        if($uploadedFile->getSize() > $maxFileSize)
        {
            throw new ValidationException(['transaction' => ['Maximum allowed size is 20 MB']]);
        }

        $allowedMimeTypes = ['text/csv','application/vnd.oasis.opendocument.spreadsheet'];

        if(! in_array($uploadedFile->getClientMediaType(), $allowedMimeTypes))
        {
            throw new ValidationException(['transaction' => ['Please select a CSV file to import']]);
        }

        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($uploadedFile->getStream()->getMetadata('uri'));

        if(! in_array($mimeType, $allowedMimeTypes))
        {
            throw new ValidationException(['transaction' => ['An invalid file type!']]); 
        }

        return $data;
    }
}
