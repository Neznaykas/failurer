<?php 

declare(strict_types=1);

ini_set("memory_limit", "512M");
ini_set('max_execution_time', '0');

use Failure\LogParser;
use Failure\Generator;

require_once __DIR__ . '/vendor/autoload.php';

/* Console mod */
if (isset($argv)) {
    $commands = [];

    for ($i = 1; $i < $argc; $i++) {
        $commands[$i - 1] = $argv[$i];
    }

    try {
        (new LogParser("php://stdin", floatval($commands[1]), floatval($commands[3])))->print();
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }

} else {
    /* Interactive: localhost */
    $time_start = microtime(true);

    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <div class="container">';

    $logfile = __DIR__ . '/access.log';
    $nginx_log = '/var/log/nginx/localhost.access_log';

    if (file_exists($nginx_log)) {
        if (filesize($nginx_log) < 10000) {
            if (rand(1, 25) == 1) {
                header("HTTP/1.1 500 Internal Server Error");
                header("Refresh:0");
            } else {
                header("Refresh:0");
            }
        }

        echo '<b>Nginx analyze: </b><br>';
        $logs = new LogParser($nginx_log, 100.0, 1.0);
        echo 'Count: ' . $logs->count . ' Errors: ' . $logs->errors . ' Intervals: ' . count($logs->intervals->items) . '<br><hr>';

        $logs->print('<br>');

        //echo file_get_contents($nginx_log);
    }

    echo '<hr><b>Random generate analyze: </b><br>';
    new Generator($logfile, 2300, 100);

    $logs = new LogParser($logfile, 99.9, 100);
    echo 'Count: ' . $logs->count . ' Errors: ' . $logs->errors . '<br>';

    $logs->print('<br>');

    $time_end = microtime(true);
    $execution_time = ($time_end - $time_start);
    echo '<hr><b>Total Execution Time:</b> ' . $execution_time;
    echo '<br><br></div></html>';
}
