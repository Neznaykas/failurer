<?php

namespace Failure;

use PHPUnit\Framework\TestCase;

use Failure\Model\Interval;
use Failure\Model\Intervals;

class IntervalsTest extends TestCase
{
    public function testSortIntervals()
    {
        $sorted = new Intervals();
        $sorted->add(new Interval(5, 10, 10));
        $sorted->add(new Interval(4, 9, 10));
        $sorted->sort();

        $unsort = new Intervals();
        $unsort->add(new Interval(4, 9, 10));
        $unsort->add(new Interval(5, 10, 10));

        $this->assertIsArray($sorted->get());
        $this->assertIsArray($unsort->get());
        
        $this->assertEqualsCanonicalizing($sorted, $unsort);
    }

}
