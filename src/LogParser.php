<?php

declare(strict_types=1);

namespace Failure;

use DateTime;

use Failure\Model\Intervals;
use Failure\Model\Interval;

class LogParser
{
    private $handle;
    public float $uptime;
    private float $timeout;
    private int $inteval;
    private bool $thread;
    public Intervals $intervals;
    public int $count = 0;
    public int $errors = 0;

    public function __construct(string $file, float $needed_uptime, float $timeout, int $interval = 0, $thread = false)
    {
        $this->uptime = $needed_uptime;
        $this->timeout = $timeout;
        $this->inteval = $interval;
        $this->thread = $thread;

        if (!$thread) /* if need analitics */
            $this->intervals = new Intervals();

        $this->handle = fopen($file, "r");

        if (!$this->handle)
            throw new \Exception('Не удалось открыть файл');
    }

    public function run()
    {
        if ($this->thread) {
            while (true) {
                $this->parse();
            }
        } else {
            $this->parse();
        }
        return $this;
    }

    private function parse()
    {
        $start_date = 0;
        $errors = 0;
        $count = 0;

        try {
            while (($buffer = fgets($this->handle, 4096)) !== false) {
                
                $buffer = explode(" ", $buffer, 11);
                //$buffer = preg_split('/ /', $buffer);

                if (count($buffer) < 10)
                    continue;

                //$date = strtotime($buffer[3]);
                $date = DateTime::createFromFormat('[d/m/Y:H:i:s', $buffer[3])->getTimestamp();
                
                $request_time = $buffer[10];
                $status = $buffer[8];

                if (($status > 499 && $status < 600) || $request_time >= $this->timeout) {
                    $errors++;
                    $this->errors++;

                    if ($start_date == 0)
                        $start_date = $date;

                    if ($count == 0) {
                        $count++;
                    } else {
                        $uptime = (($count - $errors) * 100) / $count;

                        if ($this->analize($start_date, $date, $uptime)) {
                            $start_date = 0;
                            $count = 0;
                            $errors = 0;
                        }
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
        } finally {
            fclose($this->handle);
        }
        return $this;
    }

    private function analize($start, $end, $uptime)
    {
        if ($uptime <= $this->uptime && abs($end - $start) > (60 * $this->inteval)) {
            if ($this->thread)
                echo date('H:i:s', $start) . ' - ' . date('H:i:s', $end) . ' | ' . number_format($uptime, 1) . PHP_EOL;
            else
                $this->intervals->add(new Interval($start, $end, $uptime));

            return true;
        }
        return false;
    }

    public function print(string $delimetr = PHP_EOL)
    {
        foreach ($this->intervals->get() as $interval) {
            echo date('H:i:s', $interval->start) . ' - ' . date('H:i:s', $interval->end) . ' | ' . number_format($interval->uptime, 1) . $delimetr;
        }
    }
}
