<?php

declare(strict_types=1);

namespace App;

use ReflectionMethod;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

class RouteEntityBindingStrategy implements InvocationStrategyInterface
{
    function __invoke(
        callable $callable, 
        ServerRequestInterface $request, 
        ResponseInterface $response, 
        array $routeArguments
        ): ResponseInterface
    {
        $callableReflection = $this->createReflectionForCallable($callable);
        $resolvedArguments = [];

        foreach($callableReflection->getParameters() as $parameter)
        {
            $type = $parameter->getType();
            $name = $parameter->getName();
            
        }

        return $callable($request, $response, $routeArguments);
    }

    public function createReflectionForCallable(callable $callable): ReflectionFunctionAbstract
    {
        return is_array($callable)
            ? new ReflectionMethod($callable[0], $callable[1])
            : new ReflectionFunction($callable);
    }
}
