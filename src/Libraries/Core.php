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

    protected static $validtimezones = [];

    // Valid formats for time intervals
    // Includes divisor for converting from seconds
    protected static $formats = [
        'seconds' => 1,
        'minutes' => 60,
        'hours' => 3600,
        'days' => 86400,
        'weeks' => 86400 * 7,
        'years' => 86400 * 365.25, // Include leap-years in calculations
    ];

    public function __construct()
    {
        // Store all timezones for validation later
        self::$validtimezones = DateTimeZone::listIdentifiers();
    }

    /**
     * Set the CLI Instance
     *
     * @param object $cli Instance of CLI Class
     */
    public function setCLIInstance(CLI $cli)
    {
        $this->cli = $cli;
    }

    /**
     * Set the default Timezone
     *
     * @param string $timezone Valid PHP Timezone identifier
     */
    public function setDefaultTimezone($timezone)
    {
        $this->tzdefault = $timezone;
        $this->dates['from']['tz'] = $timezone;
        $this->dates['to']['tz'] = $timezone;
    }

    /**
     * Prompt the user to enter a 'From' Date
     */
    public function requestFromDate()
    {
        $this->dates['from'] = $this->requestDate('From');
    }

    /**
     * Prompt the user to enter a 'To' Date
     */
    public function requestToDate()
    {
        $this->dates['to'] = $this->requestDate('To');
    }

    /**
     * Force the 'From' Date (used by unit tests, etc)
     *
     * @param string $date   Date string
     * @param string $format PHP Date Format to parse with
     * @param string $tz    (optional) override Timezone
     */
    public function setFromDate($date, $format = 'Y-m-d', $tz = false)
    {
        $this->setDate('from', $date, $format, $tz);
    }

    /**
     * Force the 'To' Date (used by unit tests, etc)
     *
     * @param string $date   Date string
     * @param string $format PHP Date Format to parse with
     * @param string $tz    (optional) override Timezone
     */
    public function setToDate($date, $format = 'Y-m-d', $tz = false)
    {
        $this->setDate('to', $date, $format, $tz);
    }

    /**
     * Set the date (used setFromDate() and setToDate())
     *
     * @param string $type Type of date to set (from/to)
     */
    protected function setDate($type, $date, $format = 'Y-m-d', $tz = false)
    {
        $tz = ($tz) ?: $this->tzdefault;
        $date = DateTime::createFromFormat($format, $date, new DateTimeZone($tz));
        $this->dates[$type] = [
            'date' => $date,
            'tz' => $tz,
        ];
    }

    /**
     * Validate a passed Timezone string
     *
     * @param string $timezone Timezone for Validation
     * @return boolean
     */
    public function validateTimezone($timezone)
    {
        return in_array($timezone, self::$validtimezones);
    }

    /**
     * Prompt the User for a Date and Optional Timezone
     *
     * @param string $type Type of Date to prompt for (from/to)
     * @return void
     */
    protected function requestDate($type)
    {
        $this->cli->question("What is the {$type} Date?", "Acceptable Formats: YYYY-MM-DD, YYYY-MM-DD HH:MM:SS");

        $date = $this->cli->getInput("\t{$type} Date:");

        // Check that the provided date matches one of the expected formats
        $parsed = $this->parseDate($date);
        if (!$parsed) {
            // Failed to Parse, show error and repeat process
            $this->cli->error("'{$date}' is not a valid Date, please try again");
            return $this->requestDate($type);
        }

        // Prompt for Timezone override
        $timezone = $this->tzdefault;
        $override = $this->cli->getConfirmation("\tWould you like to override the Timezone? (Default {$timezone})");

        if ($override) {
            $this->cli->question(
                "\tWhat is the Timezone for the {$type} Date?",
                "Must be a valid PHP Timezone, eg 'Africa/Algiers'"
            );
            $timezone = $this->cli->promptWithValidation(
                "\tNew Timezone:",
                [__CLASS__, 'validateTimezone'],
                "'<VALUE>' is not a valid Timezone, please try again"
            );
        }

        // Create a DateTime instance with all provided details
        $date = DateTime::createFromFormat($parsed['format'], $parsed['date'], new DateTimeZone($timezone));

        return [
            'date' => $date,
            'tz' => $timezone,
        ];
    }

    /**
     * Prompt the User to specify another format for the results
     * eg 'show output in hours'
     *
     * @return void
     */
    public function requestOutputFormat()
    {
        $this->cli->question("Would you like to return the results in an additional time interval?");
        $outputformat = $this->cli->getInput("Format (Seconds, Minutes, Hours, Years)", array_keys(self::$formats));
        $this->outputformat = trim(strtolower($outputformat));
    }

    /**
     * Parse a Date String to see if it conforms to one of our expected formats
     *
     * @param string $date User-provided date string
     * @return void
     */
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
                // NOTE: We set the time to midnight if unspecified
                // This prevents time elapsing between from/to user prompts
                'date' => $date . ' 00:00:00',
                'format' => 'Y-m-d H:i:s',
            ];
        } else {
            // Invalid
            return false;
        }
    }

    /**
     * Return either the from/to date in a human-readable format
     *
     * @param string $type Type of date to return (from/to)
     * @return string Formatted Date (or false)
     */
    public function getDate($type)
    {
        $date = isset($this->dates[$type]['date']) ? $this->dates[$type]['date'] : null;
        if ($date) {
            return $date->format('jS M Y, H:i:sA T');
        }
        return false;
    }

    /**
     * Return the Difference between the From and To Dates
     *
     * @return DateInterval Difference between dates
     */
    public function getDifference()
    {
        return $this->dates['from']['date']->diff($this->dates['to']['date']);
    }

    /**
     * Return a formatted string containing the date difference
     *
     * @return string Difference
     */
    public function getFormattedDifference()
    {
        $diff = $this->getDifference();
        $format = '%d days, %h hours, %i minutes, %s seconds';
        if ($diff->days >= 365) {
            $format = "%y years, {$format}";
        }
        return $diff->format($format);
    }

    /**
     * Return the Number of Days between both dates
     *
     * @return float Difference in Days
     */
    public function getDaysBetween()
    {
        $diff = $this->getDifference();
        return $diff->days;
    }

    /**
     * Return the Number of Complete Weeks between both dates
     *
     * @return float Difference in Weeks
     */
    public function getWeeksBetween()
    {
        $days = $this->getDaysBetween();
        // Round down to get complete weeks
        $weeks = floor($days / 7);
        return $weeks;
    }

    /**
     * Return the Number of Weekdays between both dates
     *
     * @return float Difference in Weekdays
     */
    public function getWeekdaysBetween()
    {
        $days = 0;

        // NOTE: We need to work with a clone of the 'From'
        // DateTime instance to preserve the original during the loop
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

    /**
     * Convert from one difference format (eg 'weeks') into another (eg 'hours')
     *
     * @param float $input        Input Difference (eg '60')
     * @param string $inputformat Input Difference Format (eg 'minutes')
     * @return string Difference with format (eg '23 hours')
     */
    public function convert($input, $inputformat)
    {
        if ($inputformat == 'W') {
            // NOTE: 'Weeks' (W) are not supported by DateInterval
            // Convert to Days
            $input *= 7;
            $inputformat = 'D';
        }

        // Create DateInterval using Input and Input Format
        // eg 'P86400S' is 86400 seconds
        $interval = new DateInterval("P{$input}{$inputformat}");

        // Convert to seconds
        $seconds = $this->convertToSeconds($interval);

        // Divide seconds by divisor for output format
        $interval = $seconds / self::$formats[$this->outputformat];

        // Round to 2 decimal places
        $interval = round($interval, 2);

        $output = "{$interval} " . $this->outputformat;
        return $output;
    }

    /**
     * Convert DateInterval into Seconds
     *
     * @param DateInterval $interval Input Interval
     * @return float Interval in Seconds
     */
    public function convertToSeconds(DateInterval $interval)
    {
        return ($interval->d * 86400)
             + ($interval->h * 3600)
             + ($interval->i * 60)
             + ($interval->s);
    }
}
