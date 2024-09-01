<?php

namespace App\Controllers;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class CategoriesController
{
    public function __construct(private readonly Twig $twig)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'categories/index.twig',
        );
    }

    public function store(Request $request, Response $response): Response
    {
        return $response
                ->withHeader('Location', '/categories')
                ->withStatus(302);
    }

    public function delete(Request $request, Response $response): Response
    {
        return $response
                ->withHeader('Location', '/categories')
                ->withStatus(302);
    }
}