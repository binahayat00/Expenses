<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Entity\User;
use App\Entity\Category;
use App\DataObjects\DataTableQueryParams;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CategoryService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
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

        return $category;
    }

    public function getAll(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
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

        if (! empty($params->searchTerm))
        {
            $search = addcslashes($params->searchTerm,'%_');
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
        return $this->entityManager->find(Category::class, $id);
    }
    public function edit(Category $category, string $name): Category
    {
        $category->setName($name);

        return $category;
    }

    public function update(Category $category, string $name): Category
    {
        $category = $this->edit($category, $name);

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
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        $categoryMap = [];

        foreach($categories as $category)
        {
            $categoryMap[strtolower($category->getName())] = $category;
        }

        return $categoryMap;
    }
}
