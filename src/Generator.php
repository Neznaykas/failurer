<?php

declare(strict_types=1);

namespace Failure;

use Exception;


class Generator
{
    //Simple generate logs - for tests
    public function __construct(string $filename, int $count, int $errors)
    {
        //a - end, w - rewrite
        if (!$handle = fopen($filename, "w"))
            throw new Exception('Файл не найден');

        $timestart = rand(time() - (7 * 24 * 60 * 60), time());

        if ($handle) {
            for ($i = 0; $i < $count; $i++) {
                $status = 200;
                $type = 'GET';

                if (rand(1, $errors) == 1)
                    $status = rand(500, 599);

                $timestart += rand(1, 60);

                $date = date("d/m/Y:H:i:s", $timestart); //14/06/2017:16:47:02
                $exec = number_format(rand(10, 60) + rand(1, 100) / 100, 4);
                $ip = long2ip(rand(0, 4294967295));

                if (rand(0, 2))
                    $type = rand(0, 2) ? 'POST' : 'PUT';

                $log = $ip . ' - - [' . $date . ' +1000] "' . $type . ' /rest/v1.4/documents?zone=default&_rid=e356713 HTTP/1.1" ' . $status . ' 2 ' . $exec . ' "-" "@list-item-updater" prio:0';
                $log .= PHP_EOL;

                if (fwrite($handle, $log) === FALSE) {
                    throw new Exception('Не могу произвести запись в файл');
                }
            }
            fclose($handle);
        }
    }
}
