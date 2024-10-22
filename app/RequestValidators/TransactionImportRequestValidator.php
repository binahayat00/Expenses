<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;

class TransactionImportRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $uploadedFile = $data['transaction'] ?? null;

        if(! $uploadedFile){
            throw new ValidationException(['importFile' => ['Please select a file to import!']]);
        }

        
    }
}
