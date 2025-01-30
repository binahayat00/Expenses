<?php

namespace App\RequestValidators;

use Valitron\Validator;
use App\Exception\ValidationException;

class TwoFactorLoginRequestValidator
{
    public function validate(array $data)
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
