<?php

ini_set("memory_limit","64M");
ini_set('max_execution_time', 0);

use Farpost\Logger;
use Farpost\Generator;

use function PHPUnit\Framework\fileExists;

require_once __DIR__ . '/vendor/autoload.php';

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '. $unit[$i];
}

//50 000 000
$count = 60;

if (rand(1, 10) == 1)
    header("HTTP/1.1 500 Internal Server Error");

$logfile = __DIR__ . '/access.log';

echo '<b>Start memory: </b>' . convert(memory_get_usage(true)) . '<br>';
echo ' <b>Value: </b>' . $count .  '<br>';

$nginx_log = '/var/log/nginx/localhost.access_log';

if (file_exists($nginx_log))
{
    echo file_get_contents($nginx_log);
    (new Logger($nginx_log, 60, 50))->sort()->print();
} else
    echo 'Не найден файл логов, nginx - только сгенерированый анализ' . '<br>';

/*$filters = array_fill(0, 3, null);
 
for($i = 1; $i < $argc; $i++) {
    $filters[$i - 1] = $argv[$i];
}*/

$time_start = microtime(true);

//if ($filters[1])
//new Generator($logfile, $count);

//new Logger($logfile, 95, 150);

echo '<b>Final memory: </b>' . convert(memory_get_usage(true)) . '<br>';

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo '<b>Total Execution Time:</b> ' . $execution_time . '<br><br>';


