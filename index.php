<?php

declare(strict_types=1);

ini_set("memory_limit", "512M");
ini_set('max_execution_time', '0');

use Failure\LogParser;
use Failure\Generator;

require_once __DIR__ . '/vendor/autoload.php';

/* Interactive */
$time_start = microtime(true);
$logfile = __DIR__ . '/access.log';
$nginx_log = '/var/log/nginx/localhost.access_log';

if (file_exists($nginx_log)) {
    if (filesize($nginx_log) < 12000) {
        if (rand(1, 15) == 1) {
            header("HTTP/1.1 500 Internal Server Error");
            header("Refresh:0");
        } else {
            header("Refresh:0");
        }
    }
    $nginx = new LogParser($nginx_log, 100, 1, 5, false, false, '<br>');
}

/* You can use run('a') - refresh localhost and see intervals in cli and command tail */
(new Generator($logfile, 1000, 5, 60))->run('w');

/* if need intervals analytics */
$random = new LogParser($logfile, 99.9, 60, 0, false, true);
$random->run();

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Logs Analizer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <main class="container">
        <section class="row">
            <?php if (isset($nginx)) : ?>
                <div class="col">
                    <p><b>Nginx analyze: </b> (no save to memory) </p>
                    <hr>
                    <?php $nginx->run(); ?>
                    <hr>
                    <p>Count: <?= $nginx->count ?> Errors: <?= $nginx->errors ?></p>
                </div>
            <?php endif; ?>
            <div class="col">
                <b>Random generate analyze: </b>
                <hr>
                <?php
                foreach ($random->intervals()->get() as $interval) {

                    $diff = date('i:s', abs($interval->end - $interval->start));

                    $startdate = date('H:i:s', $interval->start);
                    $enddate = date('H:i:s', $interval->end);
                    $uptime = number_format($interval->uptime, 1);

                    echo "{$startdate} - {$enddate} | {$uptime}% - {$diff}<br>";
                }
                ?>
                <hr>
                <p>Count: <?= $random->count ?> Errors: <?= $random->errors ?> Intervals: <?= $random->intervals()->count() ?></p>
                <hr>
                <p><b>Total Execution Time: </b><?= $execution_time ?></p>
                <br><br>
            </div>
        </section>
    </main>
</body>

</html>