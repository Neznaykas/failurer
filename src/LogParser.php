<?php

namespace Failure;

use DateTime;

use Failure\Model\Intervals;
use Failure\Model\Interval;

class LogParser
{
    public Intervals $intervals;
    private float $uptime;
    public int $count = 0;
    public int $errors = 0;

    public function __construct(string $file, float $needed_uptime, float $timeout)
    {
        $this->intervals = new Intervals();
        $this->uptime = $needed_uptime;

        $start_date = 0;
        $errors = 0;
        $count = 0;
        $interval_size = 0;

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
                } else
                {
                    if ($count > 0 && $errors > $interval_size) {
                        $this->analize($start_date, $date, $count, $errors);
                        $start_date = 0;
                        $count = 0;
                        $errors = 0;
                    }
                }

                $count++;
                $this->count++;
                unset($buffer);
            }

            if ($errors > 0 && $interval_size > 0) {
                $this->analize($start_date, $date, $count, $errors);
            }

            fclose($handle);
        }
    }

    private function analize($start, $end, $count, $errors)
    {
        $uptime = number_format((($count - $errors) * 100) / $count, 1);

        if ($this->uptime > $uptime)
            $this->intervals->add(new Interval($start, $end, $uptime));
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

    public function console() 
    {
        foreach ($this->intervals->get() as $interval) {
            $stderr = fopen("php://stderr", "w");
            fwrite($stderr, "\n". date('H:i:s', $interval->start) . ' - ' . date('H:i:s', $interval->end) . ' | ' . $interval->uptime ."\n");
            fclose($stderr);
        }
    }
}
