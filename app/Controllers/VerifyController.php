<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class VerifyController
{
    public function __construct(private readonly Twig $twig)
    {

    }
    public function index(ResponseInterface $response): ResponseInterface
    {
        return $this->twig->render($response, 'auth/verify.twig');
    }

    public function verify(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // TODO
        return $response;
    }
}
