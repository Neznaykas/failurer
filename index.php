<?php

ini_set("memory_limit","64M");
ini_set('max_execution_time', 0);

use Farpost\Logger;
use Farpost\Generator;

require_once __DIR__ . '/vendor/autoload.php';

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '. $unit[$i];
}

//50 000 000
$count = 6000;
$logfile = __DIR__ . '/access.log';

echo '<b>Start memory: </b>' . convert(memory_get_usage(true)) . '<br>';
echo ' <b>Value: </b>' . $count .  '<br>';

/*$filters = array_fill(0, 3, null);
 
for($i = 1; $i < $argc; $i++) {
    $filters[$i - 1] = $argv[$i];
}*/

//if ($filters[1])
new Generator($logfile, $count);
//echo file_get_contents(__DIR__ . '/access.log');

//new Logger($logfile, 95, 150);

(new Logger($logfile, 95, 150))->sort()->run();

echo '<b>Final: </b>' . convert(memory_get_usage(true)) . '<br>';


