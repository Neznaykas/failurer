<?php

declare(strict_types=1);

namespace Failure\Model;

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

    public function set(array $items)
    {
        $this->items = $items;
        return $this;
    }

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
