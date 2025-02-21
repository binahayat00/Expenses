<?php

declare(strict_types=1);

namespace App\DataObjects;

class IncomeData
{
    public function __construct(
        public readonly float $amount,
        public readonly string $source,
        public readonly string $date,
        public readonly int $user
    ) {
    }
}
