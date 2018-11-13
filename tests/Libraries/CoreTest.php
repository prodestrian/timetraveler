<?php

use PHPUnit\Framework\TestCase;
use TimeTraveler\Libraries\Core as TimeTravelCore;

final class CoreTest extends TestCase
{
    public function testDayDifferenceCanBeCalculated()
    {
        // Same day
        $start = new DateTime('2018-01-01');
        $this->assertEquals(0, TimeTravelCore::getDaysBetween($start, $start));

        // Next Day
        $end = new DateTime('2018-01-02');
        $this->assertEquals(1, TimeTravelCore::getDaysBetween($start, $end));

        // Next Month
        $end = new DateTime('2018-02-01');
        $this->assertEquals(31, TimeTravelCore::getDaysBetween($start, $end));

        // Previous Day/Year (End before Start)
        $end = new DateTime('2017-12-31');
        $this->assertEquals(1, TimeTravelCore::getDaysBetween($start, $end));

        // 1 second less than two full days
        $start = new DateTime('2018-05-01 00:00:00');
        $end = new DateTime('2018-05-02 23:59:59');
        $this->assertEquals(1, TimeTravelCore::getDaysBetween($start, $end));

        // February (non-Leap Year)
        $start = new DateTime('2018-02-28 00:00:00');
        $end = new DateTime('2018-03-01 00:00:00');
        $this->assertEquals(1, TimeTravelCore::getDaysBetween($start, $end));

        // Leap Year
        $start = new DateTime('2016-02-28 00:00:00');
        $end = new DateTime('2016-03-01 00:00:00');
        $this->assertEquals(2, TimeTravelCore::getDaysBetween($start, $end));
    }
}
