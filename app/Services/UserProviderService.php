<?php

declare(strict_types=1);

namespace App\Services;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use App\Contracts\UserInterface;
use App\DataObjects\RegisterUserData;
use App\Contracts\UserProviderServiceInterface;

class UserProviderService implements UserProviderServiceInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }
    public function getByCredentials(array $credentials): ?UserInterface
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
    }
    public function getById(int $userId): ?UserInterface
    {
        return $this->entityManager->find(User::class, $userId);
    }

    public function createUser(RegisterUserData $data): UserInterface
    {
        $user = new User();

        $user->setName($data->name);
        $user->setEmail($data->email);
        $user->setPassword(password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function countOfUsersByEmail(string $email): ?int
    {
        return $this->entityManager->getRepository(User::class)->count(
            ['email' => $email]
        );
    }
}
