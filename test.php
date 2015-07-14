<?php

require_once __DIR__ . '/../../../vendor/autoload.php'; // Autoload files using Composer autoload

use Fairy\Sys\Noah;

echo "hello";
// echo SayHello::world();
//var_dump(Noah::db());
// Go to the terminal (or create a PHP web server inside "tests" dir) and type:

Noah::db();
// php tests/test.php
