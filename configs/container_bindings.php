<?php

declare(strict_types=1);

use App\Config;
use function DI\create;

return [
    Config::class => create(Config::class)->constructor(require CONFIG_PATH . "/app.php"),
];