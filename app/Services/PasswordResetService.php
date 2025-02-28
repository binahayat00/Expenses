<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Entity\PasswordReset;
use Doctrine\ORM\Query\Parameter;
use App\Contracts\EntityManagerServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;

class PasswordResetService
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManagerService,
        private readonly HashService $hashService,
    ) {
    }

    public function generate(string $email): PasswordReset
    {
        $passwordReset = new PasswordReset();

        $token = bin2hex(random_bytes(32));

        $passwordReset->setToken((string) $token);
        $passwordReset->setExpiration(new \DateTime('+30 minutes'));
        $passwordReset->setEmail($email);

        $this->entityManagerService->sync($passwordReset);

        return $passwordReset;
    }

    public function deactivateAllPasswordResets(string $email): void
    {
        $this->entityManagerService->getRepository(PasswordReset::class)
            ->createQueryBuilder('pr')
            ->update()
            ->set('pr.isActive', 0)
            ->where('pr.email =:email')
            ->andWhere('pr.isActive = 1')
            ->setParameter('email', $email)
            ->getQuery()
            ->execute();
    }

    public function findByToken(string $token): ?PasswordReset
    {
        return $this->entityManagerService->getRepository(PasswordReset::class)
            ->createQueryBuilder('pr')
            ->select('pr')
            ->where('pr.token = :token')
            ->andWhere('pr.isActive = :active')
            ->andWhere('pr.expiration > :now')
            ->setParameters(
                new ArrayCollection(array(
                    new Parameter('token', $token),
                    new Parameter('active', true),
                    new Parameter('now', new \DateTime()),
                ))
            )
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function updatePassword(User $user, string $password)
    {
        $this->entityManagerService->wrapInTransaction(function () use ($user, $password) {

            $this->deactivateAllPasswordResets($user->getEmail());
            
            $user->setPassword($this->hashService->hashPassword($password));

            $this->entityManagerService->sync($user);

        });
    }
}
