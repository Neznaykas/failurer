<?php

namespace Farpost;

use DateTime;

class Logger
{
    public $intervals;
    private $uptime;

    public function __construct($file, $needed_uptime, $timeout)
    {
        $this->intervals = [];
        $this->uptime = $needed_uptime;

        $start = 0;
        $errors = 0;
        $count = 0;

        $handle = fopen($file, "r") or die("Couldn't get handle");

        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {

                $buffer = explode(" ", $buffer);
                $date = DateTime::createFromFormat('[d/m/Y:H:i:s', $buffer[3])->getTimestamp(); //3 -date
                $duration = $buffer[10];
                $status = $buffer[8];

                if ($duration <= $timeout && $status == 200 && $errors > 0) { //? Interval size
                    //Y:m:d H:i:s
                    $time = number_format((($count - $errors) * 100) / $count, 1);

                    if ($this->uptime > $time) {
                        //echo date('H:i:s', $start) . ' - ' . date('H:i:s', $date) . ' | ' . $time . '%<br>';

                        //$this->logs[] = gzencode(json_encode(new Intervals($start, $date, $time)));
                        $this->intervals[] = new Interval($start, $date, $time);
                    }

                    $count = 0;
                    $errors = 0;
                }

                if (($status > 499 && $status < 600) || $duration >= $timeout) {
                    if ($errors == 0) {
                        $start = $date;
                        $errors = 1;
                        //$count = 0;
                    } else
                        $errors++;
                }

                $count++;
                unset($buffer);
            }

            if ($errors > 0) {
                $time = number_format((($count - $errors) * 100) / $count, 1);

                if ($this->uptime > $time) {
                    $this->intervals[] = new Interval($start, $date, $time);
                }
            }

            fclose($handle);
        }
    }

    public function sort(bool $increase = true)
    {
        if ($increase)
            usort($this->intervals, function ($a, $b) {
                return $a->end > $b->end;
            });
        else
            usort($this->intervals, function ($a, $b) {
                return $a->end <=> $b->end;
            });

        return $this;
    }

    public function print()
    {
        foreach ($this->intervals as $interval) {
            echo date('H:i:s', $interval->start) . ' - ' . date('H:i:s', $interval->end) . ' | ' . $interval->uptime . '<br>';
        }
    }
}
