<?php

namespace App\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use App\Entity\Traits\HasTimestamps;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;


#[Entity, Table('incomes')]
#[HasLifecycleCallbacks]
class Income
{
    use HasTimestamps;
    
    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(name: 'amount', type: Types::DECIMAL, precision: 13, scale: 3)]
    private float $amount;

    #[Column]
    private string $source;

    #[Column]
    private \DateTime $date;

    #[Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[Column(name: 'updated_at')]
    private \DateTime $updatedAt;

    #[ManyToOne(inversedBy: 'incomes')]
    private User $user;
    
}
