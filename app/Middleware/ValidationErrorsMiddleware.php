<?php

namespace App\Middleware;

use App\Contracts\SessionInterface;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationErrorsMiddleware implements MiddlewareInterface 
{
    public function __construct(
        private readonly Twig $twig,
        private readonly SessionInterface $session
        )
    {
        
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if($errors = $this->session->getFlash('errors'))
        {
            $this->twig->getEnvironment()->addGlobal('errors', $errors);
        }
        return $handler->handle($request);
    }
}
