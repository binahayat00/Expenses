<?php

namespace App\Entity;

use App\Contracts\OwnableInterface;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use App\Entity\Traits\HasTimestamps;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;

#[Entity, Table('categories')]
#[HasLifecycleCallbacks]

class Category implements OwnableInterface
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column]
    private string $name;

    #[OneToMany(mappedBy: 'category', targetEntity: Transaction::class)]
    private Collection $transactions;
    
    #[ManyToOne(inversedBy: 'categories')]
    private User $user;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Category
    {
        $this->name = $name;

        return $this;
    }

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): Category
    {
        $this->transactions->add($transaction);

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Category
    {
        $user->addCategory($this);

        $this->user = $user;

        return $this;
    }
}
