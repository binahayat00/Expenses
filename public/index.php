<?php

declare(strict_types=1);

use Slim\App;

$container = require dirname(__DIR__) ."/bootstrap.php";

$container->get(App::class)->run();