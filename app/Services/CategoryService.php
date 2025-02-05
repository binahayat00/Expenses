<?php

declare(strict_types=1);

namespace App\Services;

use App\Session;
use App\Entity\User;
use App\Entity\Category;
use App\Contracts\SessionInterface;
use Psr\SimpleCache\CacheInterface;
use App\DataObjects\DataTableQueryParams;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Contracts\EntityManagerServiceInterface;

class CategoryService
{
    private string $cacheKey;

    public function __construct(
        private readonly EntityManagerServiceInterface $entityManager,
        private readonly CacheInterface $cache,
        private readonly SessionInterface $session,
    ) {
        $this->cacheKey = 'categories_keyed_by_name_' . $this->session->get('user');
    }
    public function make(string $name, User $user): Category
    {
        $category = new Category();

        $category->setUser($user);

        return $this->edit($category, $name);
    }

    public function create(string $name, User $user): Category
    {
        $category = $this->make($name, $user);

        $this->entityManager->persist($category);

        $this->cache->delete($this->cacheKey);

        return $category;
    }

    public function getAll(): array
    {        
        if ($this->cache->has($this->cacheKey)) {
            return $this->cache->get($this->cacheKey);
        }
        
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        
        $this->cache->set($this->cacheKey,$categories);

        return $categories;
    }

    public function getPaginatedCategories(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Category::class)
            ->createQueryBuilder('c')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy = in_array($params->orderBy, ['name', 'createdAt', 'updatedAt']) ? $params->orderBy : 'updatedAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (!empty($params->searchTerm)) {
            $search = addcslashes($params->searchTerm, '%_');
            $query->where('c.name LIKE :name')->setParameter(
                'name',
                "%$search%"
            );
        }

        $query->orderBy('c.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function getById(int $id): ?Category
    {        
        if ($this->cache->has($this->cacheKey)) {
            return $this->cache->get($this->cacheKey);
        }

        return $this->entityManager->find(Category::class, $id);
    }
    public function edit(Category $category, string $name): Category
    {
        $category->setName($name);

        $this->cache->delete($this->cacheKey);

        return $category;
    }

    public function update(Category $category, string $name): Category
    {
        $category = $this->edit($category, $name);

        $this->cache->delete($this->cacheKey);

        return $category;
    }

    public function getCategoryName(): array
    {
        return $this->entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select('c.id', 'c.name')
            ->getQuery()
            ->getArrayResult();
    }

    public function findByName(string $name): ?Category
    {
        return $this->entityManager->getRepository(Category::class)
            ->findBy(['name' => $name])[0] ?? null;
    }

    public function getAllKeyedByName(): array
    {
        if ($this->cache->has($this->cacheKey)) {
            return $this->cache->get($this->cacheKey);
        }

        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        $categoryMap = [];

        foreach ($categories as $category) {
            $categoryMap[strtolower($category->getName())] = $category;
        }

        $this->cache->set($this->cacheKey,$categoryMap);

        return $categoryMap;
    }

    public function getTopSpendingCategories(int $limit): array
    {
        // TODO: Implement

        return [
            ['name' => 'Category 1', 'total' => 700],
            ['name' => 'Category 2', 'total' => 550],
            ['name' => 'Category 3', 'total' => 475],
            ['name' => 'Category 4', 'total' => 325],
        ];
    }
}
