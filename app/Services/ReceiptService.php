<?php

declare(strict_types=1);

namespace App\Services;
use App\Entity\Receipt;
use Doctrine\ORM\EntityManager;

class ReceiptService extends EntityManagerService
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly Receipt $receipt
    )
    {
        parent::__construct($entityManager);
    }

    public function create($transaction, string $filename, string $storageFilename, string $mediaType): Receipt
    {
        $this->receipt->setTransaction($transaction);
        $this->receipt->setFilename($filename);
        $this->receipt->setStorageFilename($storageFilename);
        $this->receipt->setMediaType($mediaType);
        $this->receipt->setCreatedAt(new \DateTime());
        $this->receipt->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($this->receipt);

        return $this->receipt;
    }

    public function getById(int $id): ?Receipt
    {
        return $this->entityManager->find(Receipt::class , $id);
    }

    public function delete(Receipt $receipt): void
    {
        $this->entityManager->remove($receipt);
    }
}
