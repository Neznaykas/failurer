<?php

namespace Farpost;

//создать обьект для хранения лога? (как структуру)
//use Farpost\Log;
use DateTime;

class Logger
{
    private $logs;
    private $uptime;

    public function __construct($file, $needed_uptime, $timeout)
    {
        $this->logs = [];
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

                if ($duration <= $timeout && $status == 200 && $errors > 0) {
                    //Y:m:d H:i:s
                    $time = number_format(((($count - $errors) * 100) / $count), 1);

                    if ($this->uptime > $time) {
                        //echo date('H:i:s', $start) . ' - ' . date('H:i:s', $date) . ' | ' . $time . '%<br>';

                        //$this->logs[] = gzencode(json_encode(new Intervals($start, $date, $time)));
                        $this->logs[] = new Intervals($start, $date, $time);
                    }

                    $count = 0;
                    $errors = 0;
                }

                if (($status > 499 && $status < 600) || $duration >= $timeout) {
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
        usort($this->logs, function ($a, $b) {

            //$start = json_decode(gzdecode($a), false);
            //$start2 = json_decode(gzdecode($b), false);

            return $a->end > $b->end;
        });

        return $this;
    }

    public function run()
    {
        foreach ($this->logs as $log) {
           // $log = json_decode(gzdecode($log), false);
            echo date('d H:i:s', $log->start) . ' - ' . date('H:i:s', $log->end) . ' | ' . $log->uptime . '%<br>';
        }
    }
}
