<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Entity\Category;
use Doctrine\ORM\EntityManager;

class CategoryService
{
    public function __construct(private readonly EntityManager $entityManager)
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

        $this->flush($category);

        return $category;
    }

    public function flush(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    public function getAll(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
    }

    public function delete(int $id): void
    {
        $category = $this->entityManager->find(Category::class, $id);

        $this->entityManager->remove($category);
        $this->entityManager->flush();
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

        $this->flush($category);

        return $category;
    }
}
