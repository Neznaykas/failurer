<?php

namespace Farpost;

use DateTime;
use Farpost\Model\Intervals;
use Farpost\Model\Interval;

class Logger
{
    public Intervals $intervals;
    private float $uptime;

    public function __construct(string $file, float $needed_uptime, float $timeout)
    {
        $this->intervals = new Intervals();
        $this->uptime = $needed_uptime;

        $start = 0;
        $errors = 0;
        $count = 0;

        $handle = fopen($file, "r") || die("Couldn't get handle");

        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {

                $buffer = explode(" ", $buffer);

                $date = DateTime::createFromFormat('[d/m/Y:H:i:s', $buffer[3])->getTimestamp(); 
                $request_time = $buffer[10];
                $status = $buffer[8];

                if ($request_time <= $timeout && $status == 200 && $errors > 0) {
                    $uptime = number_format((($count - $errors) * 100) / $count, 1);

                    if ($this->uptime > $uptime) {
                        $this->intervals->add(new Interval($start, $date, $uptime));
                    }

                    $count = 0;
                    $errors = 0;
                }

                if (($status > 499 && $status < 600) || $request_time >= $timeout) {
                    if ($errors == 0) {
                        $start = $date;
                        $errors = 1;
                    } else
                        $errors++;
                }

                $count++;
                unset($buffer);
            }

            fclose($handle);
        }
    }

    public function sort()
    {
        $this->intervals->sort();
        return $this;
    }

    public function print()
    {
        foreach ($this->intervals->get() as $interval) {
            echo date('H:i:s', $interval->start) . ' - ' . date('H:i:s', $interval->end) . ' | ' . $interval->uptime . '<br>';
        }
    }
}
