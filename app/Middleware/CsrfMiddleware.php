<?php

namespace App\Middleware;
use Slim\Csrf\Guard;
use App\Exception\ValidationException;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    protected Guard $guard;
    public function __construct(protected ResponseFactory $responseFactory)
    {
        $this->guard = new Guard($responseFactory);
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->guard->setFailureHandler(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $request = $request->withAttribute("csrf_status", false);
            return $handler->handle($request);
        });

        if (false === $request->getAttribute('csrf_status')) 
        {
            throw new ValidationException($request->getAttribute('csrf_status'),'The Csrf Validation Error(s)',);
        }

        return $handler->handle($request);
    }
}
