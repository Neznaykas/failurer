<?php

declare(strict_types=1);

namespace Failure\Model;

class Intervals
{
    private array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function set(array $items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return Interval[]
     */
    public function get(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function sort()
    {
        usort($this->items, function ($a, $b) {
            return $a->start <=> $b->start;
        });
        return $this;
    }

    public function add(Interval $item)
    {
        $this->items[] = $item;
        return $this;
    }
}
