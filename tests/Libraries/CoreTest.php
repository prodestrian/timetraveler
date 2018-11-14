<?php

use PHPUnit\Framework\TestCase;
use TimeTraveler\Libraries\Core as TimeTraveler;

final class CoreTest extends TestCase
{
    public function testDayDifferenceCanBeCalculated()
    {
        $traveler = new TimeTraveler();
        $traveler->setDefaultTimezone('Australia/Adelaide');

        // Same day
        $traveler->setFromDate('2018-01-01');
        $traveler->setToDate('2018-01-01');
        $this->assertEquals(0, $traveler->getDaysBetween());

        // Next Day
        $traveler->setToDate('2018-01-02');
        $this->assertEquals(1, $traveler->getDaysBetween());

        // Next Month
        $traveler->setToDate('2018-02-01');
        $this->assertEquals(31, $traveler->getDaysBetween());

        // Previous Day/Year (End before Start)
        $traveler->setToDate('2017-12-31');
        $this->assertEquals(1, $traveler->getDaysBetween());

        // 1 second less than two full days
        $traveler->setFromDate('2018-05-01 00:00:00', 'Y-m-d H:i:s');
        $traveler->setToDate('2018-05-02 23:59:59', 'Y-m-d H:i:s');
        $this->assertEquals(1, $traveler->getDaysBetween());

        // February (non-Leap Year)
        $traveler->setFromDate('2018-02-28 00:00:00', 'Y-m-d H:i:s');
        $traveler->setToDate('2018-03-01 00:00:00', 'Y-m-d H:i:s');
        $this->assertEquals(1, $traveler->getDaysBetween());

        // Leap Year
        $traveler->setFromDate('2016-02-28 00:00:00', 'Y-m-d H:i:s');
        $traveler->setToDate('2016-03-01 00:00:00', 'Y-m-d H:i:s');
        $this->assertEquals(2, $traveler->getDaysBetween());
    }

    public function testMultipleTimezonesCanBeHandled()
    {
        $traveler = new TimeTraveler();

        // Same time, different timezone (0 seconds difference)
        // 10:30am Adelaide is 12:00am UTC in January
        $traveler->setFromDate('2018-01-01 10:30:00', 'Y-m-d H:i:s', 'Australia/Adelaide');
        $traveler->setToDate('2018-01-01 00:00:00', 'Y-m-d H:i:s', 'UTC');
        $this->assertEquals(0, $traveler->getDifference()->s);

        // Different times + timezones
        // 1hr later in Sydney is actually only 30 minutes
        $traveler->setFromDate('2018-01-01 10:30:00', 'Y-m-d H:i:s', 'Australia/Adelaide');
        $traveler->setToDate('2018-01-01 11:30:00', 'Y-m-d H:i:s', 'Australia/Sydney');
        $this->assertEquals(30, $traveler->getDifference()->i);
    }
}
