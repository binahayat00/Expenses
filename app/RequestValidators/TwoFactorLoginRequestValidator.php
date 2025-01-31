<?php

namespace App\RequestValidators;

use Valitron\Validator;
use App\Exception\ValidationException;
use App\Contracts\RequestValidatorInterface;

class TwoFactorLoginRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $validator = new Validator($data);

        $validator->rule('required', ['email', 'code']);
        $validator->rule('email', 'email');

        if (!$validator->validate()) {
            throw new ValidationException($validator->errors());
        }

        return $data;
    }
}
