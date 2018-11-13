<?php

namespace TimeTraveler\Libraries;

/**
 * Core DateTime comparison class
 */

use DateInterval;
use DateTime;
use DateTimeZone;

class Core
{
    protected $dates = [
        'from' => [
            'date' => null,
            'tz' => null,
        ],
        'to' => [
            'date' => null,
            'tz' => null,
        ],
    ];

    protected $tzdefault;

    protected $outputformat;

    protected $cli;

    protected static $formats = [
        'seconds' => 1,
        'minutes' => 60,
        'hours' => 3600,
        'days' => 86400,
        'weeks' => 86400 * 7,
        'years' => 86400 * 365.25, // Let's include leap-years
    ];

    public function setCLIInstance($cli)
    {
        $this->cli = $cli;
    }

    public function setDefaultTimezone($timezone)
    {
        $this->tzdefault = $timezone;
        $this->dates['from']['tz'] = $timezone;
        $this->dates['from']['tz'] = $timezone;
    }

    public function requestFromDate()
    {
        $this->dates['from'] = $this->requestDate('From');
    }

    public function requestToDate()
    {
        $this->dates['to'] = $this->requestDate('To');
    }

    protected function requestDate($type)
    {
        $this->cli->question("What is the {$type} Date?", "Acceptable Formats: YYYY-MM-DD, YYYY-MM-DD HH:MM:SS");

        $date = $this->cli->getInput("\t{$type} Date:");

        $parsed = $this->parseDate($date);
        if (!$parsed) {
            $this->cli->error("'{$date}' is not a valid Date, please try again");
            return $this->requestDate($type);
        }
        $override = $this->cli->getConfirmation("\tWould you like to override the Timezone? (Default {$this->tzdefault})");

        $timezone = $this->tzdefault;
        if ($override) {
            $this->cli->question(
                "\tWhat is the Timezone for the {$type} Date?",
                "Must be a valid PHP Timezone, eg 'Africa/Algiers'"
            );
            $timezone = $this->cli->promptForTimezone("\tNew Timezone:");
        }

        $date = DateTime::createFromFormat($parsed['format'], $parsed['date'], new DateTimeZone($timezone));

        return [
            'date' => $date,
            'tz' => $timezone,
        ];
    }

    public function requestOutputFormat()
    {
        $this->cli->question("Would you like to return the results in an additional time interval?");
        $outputformat = $this->cli->getInput("Format (Seconds, Minutes, Hours, Years)", array_keys(self::$formats));
        $this->outputformat = trim(strtolower($outputformat));
    }

    protected function parseDate($date)
    {
        if (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}$/", $date)) {
            // YYYY-MM-DD HH:MM:SS
            return [
                'date' => $date,
                'format' => 'Y-m-d H:i:s',
            ];
        } elseif (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)) {
            // YYYY-MM-DD
            return [
                'date' => $date . ' 00:00:00',
                'format' => 'Y-m-d H:i:s',
            ];
        } else {
            // Invalid
            return false;
        }
    }

    public function getDate($type)
    {
        $date = isset($this->dates[$type]['date']) ? $this->dates[$type]['date'] : null;
        if ($date) {
            return $date->format('jS M Y, H:i:sA T');
        }
        return false;
    }

    protected function getDifference()
    {
        return $this->dates['from']['date']->diff($this->dates['to']['date']);
    }

    public function getFormattedDifference()
    {
        $diff = $this->getDifference();
        return $diff->format('%d days, %h hours, %i minutes, %s seconds');
    }

    public function getDaysBetween()
    {
        $diff = $this->getDifference();
        return $diff->days;
    }

    public function getWeeksBetween()
    {
        $days = $this->getDaysBetween();
        // Round down to get complete weeks
        $weeks = floor($days / 7);
        return $weeks;
    }

    public function getWeekdaysBetween()
    {
        $days = 0;

        // NOTE: We need to work with a clone of the DateTime instance to preserve the original
        $fromdate = clone $this->dates['from']['date'];
        $todate = $this->dates['to']['date'];
        // Loop through each of the days in the difference
        while ($fromdate->diff($todate)->days > 0) {
            // Only increment count if the current day is Mon-Fri
            $days += $fromdate->format('N') < 6 ? 1 : 0;
            // Add 1 day to current date, continue loop
            $fromdate = $fromdate->add(new DateInterval("P1D"));
        }

        return $days;
    }

    public function convert($input, $inputformat)
    {
        if ($inputformat == 'W') {
            // NOTE: 'Weeks' (W) are not supported by DateInterval
            // Convert to Days
            $input *= 7;
            $inputformat = 'D';
        }

        $interval = new DateInterval("P{$input}{$inputformat}");

        // Convert to seconds
        $seconds = ($interval->d * 86400)
             + ($interval->h * 3600)
             + ($interval->i * 60)
             + ($interval->s);

        $interval = $seconds / self::$formats[$this->outputformat];

        $output = "{$interval} " . $this->outputformat;
        return $output;
    }
}
