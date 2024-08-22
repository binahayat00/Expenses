<?php

namespace App\Middleware;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationErrorsMiddleware implements MiddlewareInterface 
{
    public function __construct(private readonly Twig $twig)
    {
        
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if(! empty($_SESSION['errors']))
        {
            $this->twig->getEnvironment()->addGlobal('errors', $_SESSION['errors']);
        }
        return $handler->handle($request);
    }
}
