<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Entity\User;
use Valitron\Validator;
use App\Exception\ValidationException;
use App\Contracts\RequestValidatorInterface;
use App\Contracts\EntityManagerServiceInterface;

class ResetPasswordRequestValidator implements RequestValidatorInterface
{

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['password', 'confirmPassword']);
        $v->rule('equals', 'confirmPassword', 'password')->label('Confirm Password');

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}
