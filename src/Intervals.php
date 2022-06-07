<?php

namespace Farpost;

class Intervals
{
    public $start; //H:i:s
    public $end; //H:i:s
    public $uptime; //0 - 100%

    function __construct(string $start, string $end, int $uptime) 
    {
        $this->start = $start;
        $this->end = $end;
        $this->uptime = $uptime;
    }
}
