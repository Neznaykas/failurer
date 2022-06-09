<?php

ini_set("memory_limit", "512M");
ini_set('max_execution_time', 0);

use Farpost\Logger;
use Farpost\Generator;

require_once __DIR__ . '/vendor/autoload.php';

/* Console mod */
if (isset($argv)) {
    $commands = [];

    for ($i = 1; $i < $argc; $i++) {
        $commands[$i - 1] = $argv[$i];
    }

    (new Logger("php://stdin", floatval($commands[2]), floatval($commands[3])))->sort()->console();
} else {
    /* Interactive: localhost */
    $time_start = microtime(true);

    $logfile = __DIR__ . '/access.log';
    $nginx_log = '/var/log/nginx/localhost.access_log';

    if (file_exists($nginx_log)) {
        /* очень медленно но лог настоящий */
        if (filesize($nginx_log) < 10000) {
            if (rand(1, 100) == 1) {
                header("HTTP/1.1 500 Internal Server Error");
            } else {
                header("Refresh:0");
            }
        }

        echo '<b>Nginx analyze: </b><br>';
        (new Logger($nginx_log, 99.9, 1))->sort()->print();
    }

    echo '<b>Random generate analyze: </b><br>';
    new Generator($logfile, 100, 100);

    (new Logger($logfile, 95, 60))->print();
    
    echo '<b>Items: </b><br>';
    print_r((new Logger($logfile, 95, 60))->intervals->sort()->get());

    echo '<br><br>';

    $time_end = microtime(true);
    $execution_time = ($time_end - $time_start);
    echo '<b>Total Execution Time:</b> ' . $execution_time . '<br><br>';
}
