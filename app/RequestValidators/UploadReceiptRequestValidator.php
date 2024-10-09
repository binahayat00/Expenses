<?php

declare(strict_types=1);

namespace App\RequestValidators;
use finfo;
use App\Exception\ValidationException;
use Psr\Http\Message\UploadedFileInterface;
use App\Contracts\RequestValidatorInterface;

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

        if(! preg_match('/^[a-zA-Z0-9\s._-]+$/', $filename))
        {
            throw new ValidationException(['receipt' => 'An invalid file name !']);
        }

        $allowedMimeTypes = ['image/jpeg','image/png','application/pdf'];
        $allowedExtensions = ['pdf', 'png', 'jpeg', 'jpg'];
        $tmpFilePath = $uploadedFile->getStream()->getMetadata('uri');

        if(! in_array($uploadedFile->getClientMediaType(), $allowedMimeTypes))
        {
            throw new ValidationException(['receipt' => 'The Receipt has to be either an image or a PDF document!']);
        }

        if(! in_array($this->getExtension($tmpFilePath), $allowedExtensions))
        {
            throw new ValidationException(['receipt' => 'The Receipt has to be one of the following files: "png", "jpg", "jpeg" or "PDF" !']);
        }

        if(! in_array($this->getMimeType($tmpFilePath), $allowedMimeTypes))
        {
            throw new ValidationException(['receipt' => 'An invalid file type!']);
        }
        
        return $data;
    }

    private function getExtension(string $path): string   
    {
        return (new finfo(FILEINFO_EXTENSION))->file($path) ?: '';
    }

    private function getMimeType(string $path): string   
    {
        return (new finfo(FILEINFO_MIME_TYPE))->file($path) ?: '';
    }
}
