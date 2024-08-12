<?php

declare(strict_types=1);

namespace App;

class Config
{
    public function __construct(private readonly array $config)
    {
    }

    public function get(string $name, mixed $defaut = null): mixed
    {
        $path = explode('.' , $name);
        $value = $this->config[array_shift($path)] ?? null;

        if ($value === null) {
            return $defaut;
        }

        foreach($path as $key)
        {
            if(!isset($value[$key]))
            {
                return $defaut;
            }

            $value = $value[$key];
        }

        return $value;
    }
}
