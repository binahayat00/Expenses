<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity, Table('receipts')]
class Receipt
{
    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column]
    private string $filename;

    #[Column(name: 'storage_filename')]
    private string $storageFilename;

    #[Column(name: 'media_type')]
    private string $mediaType;

    #[Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[Column(name: 'updated_at')]
    private \DateTime $updatedAt;

    #[ManyToOne(inversedBy: 'receipts')]
    private Transaction $transaction;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): Receipt
    {
        $this->filename = $filename;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): Receipt
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): Receipt
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
 
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): Receipt
    {
        $transaction->addReceipt($this);

        $this->transaction = $transaction;

        return $this;
    }

    public function getStorageFilename(): string
    {
        return $this->storageFilename;
    }

    public function setStorageFilename($storageFilename): Receipt
    {
        $this->storageFilename = $storageFilename;

        return $this;
    }

    public function getMediaType(): string  
    {
        return $this->mediaType;
    }

    public function setMediaType($mediaType): Receipt
    {
        $this->mediaType = $mediaType;

        return $this;
    }
}
