<?php

declare(strict_types=1);

$app = require dirname(__DIR__) ."/bootstrap.php";
$router = require CONFIG_PATH ."/routes/web.php";

$router($app);

$app->run();