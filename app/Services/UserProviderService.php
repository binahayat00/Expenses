<?php

declare(strict_types=1);

namespace App\Services;
use App\Entity\User;
use App\Contracts\UserInterface;
use App\DataObjects\RegisterUserData;
use App\Contracts\UserProviderServiceInterface;
use App\Contracts\EntityManagerServiceInterface;

class UserProviderService implements UserProviderServiceInterface
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
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

        $this->entityManager->sync($user);

        return $user;
    }

    public function countOfUsersByEmail(string $email): ?int
    {
        return $this->entityManager->getRepository(User::class)->count(
            ['email' => $email]
        );
    }

    public function verifyUser(UserInterface $user): void
    {
        $user->setVerifiedAt(new \DateTime());

        $this->entityManager->sync($user);
    }
}
