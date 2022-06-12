<?php

declare(strict_types=1);

namespace Failure;

class Generator
{
    private string $filename;
    private int $count;
    private int $errors;
    private int $maxtime;

    /* Simple generate logs - for tests */
    public function __construct(string $filename, int $count, int $errors, $maxtime = 60)
    {
        $this->filename = $filename;
        $this->count = $count;
        $this->errors = $errors; //1 - 100
        $this->maxtime = $maxtime;
    }

    public function run(string $mode = 'w')
    {
        /* a - for end w - rewrite */
        if (!$handle = fopen($this->filename, $mode))
            throw new \Exception('Ошибка открытия файла, для записи');
        
        $timestart = rand(time() - (7 * 24 * 60 * 60), time());

        try {
            for ($i = 0; $i < $this->count; ++$i) {
                $status = 200;
                $type = 'GET';
                $timestart += rand(1, 60);

                $date = \date("d/m/Y:H:i:s", $timestart); //14/06/2017:16:47:02
                $exec = \rand(10, $this->maxtime) + rand(1, 100) / 100;
                $ip = \long2ip(rand(0, 4294967295));

                if (rand($this->errors, 100) === 100) {
                    if (rand(1, 2)  == 1)
                        $status = 500;
                    else
                        $exec += rand(10, 60);
                }

                $exec = number_format($exec, 4);

                if (rand(0, 2))
                    $type = rand(0, 2) ? 'POST' : 'PUT';

                $log = "{$ip} - - [{$date} +1000] \"$type /rest/v1.4/documents?zone=default&_rid=e356713 HTTP/1.1\" {$status} 2 {$exec} \"-\" \"@list-item-updater\" prio:0" . PHP_EOL;

                if(flock($handle, LOCK_EX)) {
                    if (fwrite($handle, $log) === false) {
                        throw new \Exception('Не могу произвести запись в файл');
                    }
                    flock($handle, LOCK_UN); 
                } else {
                    throw new \Exception('Файл заблокирован');
                }

            }
        } finally {
            fclose($handle);
        }
    }
}
