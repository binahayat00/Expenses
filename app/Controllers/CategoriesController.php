<?php

namespace App\Controllers;
use App\Entity\Category;
use App\Services\RequestService;
use Slim\Views\Twig;
use App\ResponseFormatter;
use App\Services\CategoryService;
use Psr\Http\Message\RequestInterface as Request;
use App\Contracts\RequestValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use App\RequestValidators\CreateCategoryRequestValidator;
use App\RequestValidators\UpdateCategoryRequestValidator;

class CategoriesController
{
    public function __construct(
        private readonly Twig $twig, 
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly CategoryService $categoryService,
        private readonly RequestService $requestService,
        private readonly ResponseFormatter $responseFormatter
        )
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response,
            'categories/index.twig',
            [
                'categories' => $this->categoryService->getAll(),
            ]
        );
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(
            CreateCategoryRequestValidator::class
        )->validate(
                $request->getParsedBody()
            );
        
        $this->categoryService->create(
            $data['name'],
            $request->getAttribute('user')
        );

        $this->categoryService->flush();

        return $response
            ->withHeader('Location', '/categories')
            ->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->categoryService->delete((int) $args['id']);
        $this->categoryService->flush();
        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryService->getById((int) $args['id']);

        if(! $category)
        {
            return $response->withStatus(404);
        }

        $data = ['id' => $category->getId(), 'name' => $category->getName()];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(
            UpdateCategoryRequestValidator::class
        )->validate(
                $args + $request->getParsedBody()
            );

        $category = $this->categoryService->getById((int) $data['id']);

        if(! $category)
        {
            return $response->withStatus(404);
        }

        $update = $this->categoryService->update($category, $data['name']);

        $this->categoryService->flush();
        
        return $this->responseFormatter->asJson($response, $data);
    }

    public function load(Request $request, Response $response): Response
    {
        $params = $this->requestService->getDataTableQueryParameters($request);

        $categories = $this->categoryService->getPaginatedCategories(
            $params
        );

        $transformer = function (Category $category){
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'createdAt' => $category->getCreatedAt()->format('m/d/Y g:i A'),
                'updatedAt' => $category->getCreatedAt()->format('m/d/Y g:i A'),
            ];
        };

        $totalCategories = count($categories);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $categories->getIterator()),
            $params->draw,
            $totalCategories,
            );

    }
}
