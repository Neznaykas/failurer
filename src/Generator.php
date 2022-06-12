<?php

declare(strict_types=1);

namespace Failure;

class Generator
{
    private string $filename;
    private int $count;
    private int $errors;
    private int $maxtime;
    private $handle;

    /* Simple generate logs - for tests */
    public function __construct(string $filename, int $count, int $errors, $maxtime = 100)
    {
        $this->filename = $filename;
        $this->count = $count;
        $this->errors = $errors; //1 - 100
        $this->maxtime = $maxtime;
    }

    public function run(string $mode = 'w')
    {
        /* a - for end w - rewrite */
        try {
            $this->handle = fopen($this->filename, $mode);
        } catch (\Throwable $th) {
            throw new \Exception('Ошибка открытия файла, для записи');
        }

        try {
            $timestart = rand(time() - (7 * 24 * 60 * 60), time());

            for ($i = 0; $i < $this->count; ++$i) {
                $status = 200;
                $type = 'GET';
                $timestart += rand(1, 60);

                $date = \date("d/m/Y:H:i:s", $timestart);
                $exec = \rand(10, $this->maxtime);
                $ip = \long2ip(rand(0, 4294967295));

                if (rand(1, 101 - $this->errors) === 1) {
                    if (rand(1, 2)  == 1) {
                        $status = 500;
                    } else {
                        $exec += rand(10, 30);
                    }
                }

                $exec = number_format($exec, 4);

                if (rand(0, 2)) {
                    $type = rand(0, 2) ? 'POST' : 'PUT';
                }

                $log = "{$ip} - - [{$date} +1000] \"$type /rest/v1.4/documents?zone=default&_rid=e356713 HTTP/1.1\" {$status} 2 {$exec} \"-\" \"@list-item-updater\" prio:0" . PHP_EOL;

                if (flock($this->handle, LOCK_EX)) {
                    if (fwrite($this->handle, $log) === false) {
                        throw new \Exception('Не могу произвести запись в файл');
                    }
                    flock($this->handle, LOCK_UN);
                } else {
                    throw new \Exception('Файл заблокирован');
                }
            }
        } finally {
            fclose($this->handle);
        }
    }
}
