<?php

declare(strict_types=1);

namespace Failure\Model;

class Intervals
{
    /**
     * @return array
     */
    public array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function set(Intervals $items)
    {
        $this->items = $items;
        return $this;
    }

    public function add(Interval $item)
    {
        $this->items[] = $item;
        return $this;
    }
}
