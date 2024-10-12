<?php

declare(strict_types=1);

namespace App\RequestValidators;
use finfo;
use App\Exception\ValidationException;
use Psr\Http\Message\UploadedFileInterface;
use App\Contracts\RequestValidatorInterface;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

class UploadReceiptRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        /** @var UploadedFileInterface $uploadedFile */
        $uploadedFile = $data['receipt'] ?? null;

        if(! $uploadedFile)
        {
            throw new ValidationException(['receipt' => 'Please select a receipt file!']);
        }

        if($uploadedFile->getError() !== UPLOAD_ERR_OK)
        {
            throw new ValidationException(['receipt' => 'Failed to upload the receipt file!']);
        }

        $maxFileSize = 5;
        $maxFileSizeInByte = $maxFileSize * 1024 * 1024;

        if($uploadedFile->getSize() > $maxFileSizeInByte)
        {
            throw new ValidationException(['receipt' => "Maximum allowed size is $maxFileSize MB !"]);
        }

        $filename = $uploadedFile->getClientFilename();

        if(! preg_match('/^[a-zA-Z0-9\s\(\)._-]+$/', $filename))
        {
            throw new ValidationException(['receipt' => 'An invalid file name !']);
        }

        $allowedMimeTypes = ['image/jpeg','image/png','application/pdf'];
        $tmpFilePath = $uploadedFile->getStream()->getMetadata('uri');

        if(! in_array($uploadedFile->getClientMediaType(), $allowedMimeTypes))
        {
            throw new ValidationException(['receipt' => 'The Receipt has to be either an image or a PDF document!']);
        }

        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromFile($tmpFilePath);

        if(! in_array($mimeType, $allowedMimeTypes))
        {
            throw new ValidationException(['receipt' => 'An invalid file type!']);
        }
        
        return $data;
    }
}
