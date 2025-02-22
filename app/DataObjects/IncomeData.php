<?php

declare(strict_types=1);

namespace App\DataObjects;

use DateTime;

class IncomeData
{
    public function __construct(
        public readonly string $source,
        public readonly float $amount,
        public readonly DateTime $date
    ) {
    }
}
