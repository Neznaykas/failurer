<?php

declare(strict_types=1);

namespace Failure;

use Failure\Model\Intervals;
use Failure\Model\Interval;

class LogParser
{
    private $handle;
    private string $filename;
    public float $uptime;
    private float $timeout;
    private int $inteval;
    private bool $thread;
    private bool $savetomemory;
    private string $out_delimetr;
    private Intervals $intervals;
    public int $count = 0;
    public int $errors = 0;

    public function __construct(string $filename, float $needed_uptime, float $timeout, int $interval = 0, $thread = false, $savetomemory = false, $out_delimetr = PHP_EOL)
    {
        $this->filename = $filename;
        $this->uptime = $needed_uptime;
        $this->timeout = $timeout;
        $this->inteval = $interval;
        $this->thread = $thread;
        $this->savetomemory = $savetomemory;
        $this->out_delimetr = $out_delimetr;
        $this->intervals = new Intervals();

        if ($thread && $savetomemory)
            trigger_error("Использование потоковой обработки с сохранением в память может вызвать недостаток памяти", E_USER_WARNING);
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

    /**
     * @return Intervals
     */
    public function intervals()
    {
        return $this->intervals;
    }

    private function parse()
    {
        $start_date = 0;
        $errors = 0;
        $count = 0;
        $uptime = 0;

        try {
            $this->handle = fopen($this->filename, "r");
        } catch (\Throwable $th) {
            throw new \Exception('Не удалось открыть файл');
        }

        try {
            while (($buffer = fgets($this->handle, 4096)) !== false) {
                if (!preg_match('/^(?P<ip>\d.+)\s...+\[(?P<time>[\d+\/ :]+)\s.+"(?P<type>\w+)\s.+"\s(?P<status>\d+).\d+\s(?P<request>\d+.\d+)/', $buffer, $matches))
                    throw new \Exception('Некоректный формат лог файла: ' . $buffer);

                /* all, ip, date/time, type request, status, request time*/
                list(,, $date,, $status, $request) = $matches;

                $date = date_create_from_format('d/m/Y:H:i:s', $date)->getTimestamp();

                if (($status > 499 && $status < 600) || $request >= $this->timeout) {
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
                unset($buffer, $matches);
            }

            if ($errors > 0) {
                $this->analize($start_date, $date, $uptime);
            }
        } finally {
            fclose($this->handle);
        }
        return $this;
    }

    private function analize($start, $end, $uptime)
    {
        if ($uptime <= $this->uptime && abs($end - $start) > (60 * $this->inteval)) {
            if ($this->savetomemory)
                $this->intervals->add(new Interval($start, $end, $uptime));
            else {
                $startdate = date('H:i:s', $start);
                $enddate = date('H:i:s', $end);
                $uptime = number_format($uptime, 1);

                echo "{$startdate} - {$enddate} | {$uptime}{$this->out_delimetr}";
            }
            return true;
        }
        return false;
    }
}
