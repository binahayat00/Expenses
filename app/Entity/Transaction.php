<?php

declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Id;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use App\Contracts\OwnableInterface;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use App\Entity\Traits\HasTimestamps;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;

#[Entity, Table('transactions')]
#[HasLifecycleCallbacks]

class Transaction implements OwnableInterface
{
    use HasTimestamps;
      
    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(name: 'was_reviewed', options: ['default' => 0])]
    private bool $wasReviewed;
    #[Column]
    private string $description;

    #[Column]
    private \DateTime $date;

    #[Column(name: 'amount', type: Types::DECIMAL, precision: 13, scale: 3)]
    private float $amount;

    #[Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[Column(name: 'updated_at')]
    private \DateTime $updatedAt;

    #[ManyToOne(inversedBy: 'transactions')]
    private User $user;
    
    #[ManyToOne(inversedBy: 'transactions')]
    private ?Category $category;

    #[OneToMany(mappedBy: 'transaction', targetEntity: Receipt::class, cascade: ['remove'])]
    private Collection $receipts;

    public function __construct()
    {
        $this->receipts = new ArrayCollection();
        $this->wasReviewed = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Transaction
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): Transaction
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): Transaction
    {
        $this->amount = $amount;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Transaction
    {
        // $user->addTransaction($this);

        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): Transaction
    {
        // $category?->addTransaction($this);

        $this->category = $category;

        return $this;
    }

    public function getReceipts(): Collection
    {
        return $this->receipts;
    }

    public function addReceipt(Receipt $receipt): Transaction
    {
        $this->receipts->add($receipt);

        return $this;
    }

    public function getWasReviewed(): bool
    {
        return $this->wasReviewed;
    }

    public function setWasReviewed(bool $wasReviewed): Transaction
    {
        $this->wasReviewed = $wasReviewed;

        return $this;
    }
}
