<?php

declare(strict_types=1);

namespace App;

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
            // TODO: Implement __invoke() method
        }
}
