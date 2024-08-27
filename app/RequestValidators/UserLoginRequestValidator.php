<?php

namespace App\RequestValidators;
use App\Exception\ValidationException;
use Valitron\Validator;
use App\Contracts\RequestValidatorInterface;

class UserLoginRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['email', 'password']);
        $v->rule('email', 'email');

        if(! $v->validate())
        {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}
