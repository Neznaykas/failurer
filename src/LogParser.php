<?php

declare(strict_types=1);

namespace Failure;

use DateTime;

use Failure\Model\Intervals;
use Failure\Model\Interval;

class LogParser
{
    public Intervals $intervals;
    public float $uptime;
    public int $count = 0;
    public int $errors = 0;

    public function __construct(string $file, float $needed_uptime, float $timeout)
    {
        $this->intervals = new Intervals();
        $this->uptime = $needed_uptime;

        $start_date = 0;
        $errors = 0;
        $count = 0;

        $handle = fopen($file, "r");

        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                $buffer = explode(" ", $buffer);

                $date = DateTime::createFromFormat('[d/m/Y:H:i:s', $buffer[3])->getTimestamp();
                $request_time = $buffer[10];
                $status = $buffer[8];

                if (($status > 499 && $status < 600) || $request_time >= $timeout) {
                    $errors++;
                    $this->errors++;

                    if ($start_date == 0)
                        $start_date = $date;

                    if ($count == 0)
                        $count++;
                    else
                        if ($this->analize($start_date, $date, $count, $errors)) {
                        $start_date = 0;
                        $count = 0;
                        $errors = 0;
                    }
                }

                if ($count > 0)
                    $count++;

                $this->count++;
                unset($buffer);
            }

            if ($errors > 0) {
                $this->analize($start_date, $date, $count, $errors);
            }
            fclose($handle);
        } else
            throw new \Exception('Не удалось открыть файл');

        $this->intervals->sort();
    }

    private function analize($start, $end, $count, $errors)
    {
        $uptime = (($count - $errors) * 100) / $count;
        //&& abs($end - $start) > 60 * 1

        if ($uptime <= $this->uptime) {
            $this->intervals->add(new Interval($start, $end, $uptime));
            return true;
        } else
            return false;
    }

    public function print(string $delimetr = PHP_EOL)
    {
        foreach ($this->intervals->items as $interval) {
            echo date('H:i:s', $interval->start) . ' - ' . date('H:i:s', $interval->end) . ' | ' . number_format($interval->uptime, 1) . $delimetr;
        }
    }
}
