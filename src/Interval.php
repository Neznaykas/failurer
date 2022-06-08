<?php

namespace Farpost;

class Interval
{
    public $start; //H:i:s
    public $end; //H:i:s
    public $uptime; //0 - 100%

    function __construct(string $start, string $end, float $uptime) 
    {
        $this->start = $start;
        $this->end = $end;
        $this->uptime = $uptime;
    }
}
