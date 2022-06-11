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

        $handle = fopen($file, "r") or die("Couldn't get handle");

        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                $buffer = explode(" ", $buffer);

                $date = DateTime::createFromFormat('[d/m/Y:H:i:s', $buffer[3])->getTimestamp();
                $request_time = $buffer[10];
                $status = $buffer[8];

                if ($start_date == 0)
                    $start_date = $date;

                if (($status > 499 && $status < 600) || $request_time >= $timeout) {
                    $errors++;
                    $this->errors++;
                } else {
                    if ($count > 0 && $errors > 0) {
                        $uptime = number_format((($count - $errors) * 100) / $count, 1);
                        if ($uptime < $this->uptime)
                            if ($this->analize($start_date, $date, $count, $errors)); {
                            $start_date = 0;
                            $count = 0;
                            $errors = 0;
                        }
                    }
                }

                $count++;
                $this->count++;
                unset($buffer);
            }

            if ($errors > 0) {
                $this->analize($start_date, $date, $count, $errors);
            }

            fclose($handle);
        }
    }

    private function analize($start, $end, $count, $errors)
    {
        $uptime = (($count - $errors) * 100) / $count;

        if ($uptime < $this->uptime) {
            $this->intervals->add(new Interval($start, $end, $uptime));
            return true;
        } else
            return false;
    }

    public function sort()
    {
        usort($this->intervals->items, function ($a, $b) {
            return $a->end > $b->end;
        });
        return $this;
    }

    public function print()
    {
        foreach ($this->intervals->items as $interval) {
            echo date('H:i:s', $interval->start) . ' - ' . date('H:i:s', $interval->end) . ' | ' . number_format($interval->uptime, 1) . '<br>';
        }
    }

    public function console()
    {
        foreach ($this->intervals->items as $interval) {
            $stderr = fopen("php://stderr", "w");
            fwrite($stderr, "\n" . date('H:i:s', $interval->start) . ' - ' . date('H:i:s', $interval->end) . ' | ' . number_format($interval->uptime, 1) . "\n");
            fclose($stderr);
        }
    }
}
