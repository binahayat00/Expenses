<?php

declare(strict_types=1);

namespace App\RequestValidators;

use Valitron\Validator;
use App\Exception\ValidationException;
use App\Contracts\RequestValidatorInterface;
use App\Contracts\UserProviderServiceInterface;

class RegisterUserRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly UserProviderServiceInterface $userProvider)
    {
        
    }
    public function validate(array $data): array
    {
        $v = new Validator($data);
        $v->rule('required', ['name', 'email', 'password', 'confirmPassword']);
        $v->rule('email', 'email');
        $v->rule('equals', 'confirmPassword', 'password')->label('Confirm Password');

        
        $v->rule(
            fn($field, $value, $params, $fields) =>
            $this->userProvider->countOfUsersByEmail($value) === 0,
            "email"
        )->message(
                "{field} failed...(User with the given email address already exists!"
            );

        if (! $v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}
