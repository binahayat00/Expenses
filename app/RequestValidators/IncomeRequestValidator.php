<?php

declare(strict_types=1);

namespace App\RequestValidators;

use Valitron\Validator;
use App\Exception\ValidationException;
use App\Contracts\RequestValidatorInterface;

class IncomeRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['amount', 'source', 'date']);
        $v->rule('dateFormat', 'dateFormat', 'm/d/Y g:i A');
        $v->rule('numeric','amount');
        $v->rule('lengthMax', 'source', 25);

        if(! $v->validate()){
            throw new ValidationException($v->errors());
        }
        
        return $data;
    }
}
