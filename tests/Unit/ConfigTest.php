<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function test_it_is_able_to_get_nested_settings(): void
    {
        $config = [
            'doctrine' => [
                'connection'=> [
                    'user'=> 'root'
                ],
            ]
        ];

        $config = new Config($config);

        $this->assertEquals('root', $config->get('doctrine.connection.user'));
        $this->assertEquals(['user' => 'root'], $config->get('doctrine.connection'));
    }
}
