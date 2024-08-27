<?php

declare(strict_types=1);

namespace App\RequestValidators;

use Psr\Container\ContainerInterface;
use App\Contracts\RequestValidatorInterface;
use App\Exception\RequestValidatorException;
use App\Contracts\RequestValidatorFactoryInterface;

class RequestValidatorFactory implements RequestValidatorFactoryInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }
    public function make(string $class): RequestValidatorInterface
    {
        $validator = $this->container->get($class);

        if ($validator instanceof RequestValidatorInterface) {
            return $validator;
        }

        throw new RequestValidatorException("Failed to instantiate the request validator calss: $class");
    }
}
