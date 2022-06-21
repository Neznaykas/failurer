<?php

declare(strict_types=1);

ini_set("memory_limit", "512M");
ini_set('max_execution_time', '0');

use Failure\LogParser;

require_once __DIR__ . '/vendor/autoload.php';

/* Console mod */
if (PHP_SAPI == "cli") {
    $threaded = false;
    $options = getopt("t:u:i:d");

    $needed_uptime = isset($options['u']) ? floatval($options['u']) : 100;
    $timeout = isset($options['t']) ? floatval($options['t']) : 100;
    $interval = isset($options['i']) ? intval($options['i']) : 0;
    $threaded = isset($options['d']) ?? true;

    try {
        (new LogParser("php://stdin", $needed_uptime, $timeout, $interval, $threaded))->run();
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }

}