<?php

namespace Failure\Model;

class Interval
{
    public int $start; //H:i:s
    public int $end; //H:i:s
    public float $uptime; //0 - 100%

    function __construct(int $start, int $end, float $uptime) 
    {
        $this->start = $start;
        $this->end = $end;
        $this->uptime = $uptime;
    }
}
