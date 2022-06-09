<?php

namespace Farpost\Model;

class Intervals
{
    /**
     * @return array
     */
    private array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function get()
    {
        return $this->items;
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

    public function sort()
    {
        usort($this->items, function ($a, $b) {
            return $a->end > $b->end;
        });
        return $this;
    }
}
