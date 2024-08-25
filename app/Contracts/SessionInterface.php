<?php

declare(strict_types=1);

namespace App\Contracts;

interface SessionInterface
{
    public function start(): void;

    public function save(): void;
    
    public function isActive(): bool;

    public function forget(string $name): void;

    public function regenerate(): bool;

    public function put(string $name, mixed $value): void;

    public function get(string $name, mixed $default = null): mixed;
    
    public function has(string $name): bool;
}
