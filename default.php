<?php

require_once "vendor/autoload.php";

use TimeTraveler\Libraries\CLI;

define('DEFAULT_TIMEZONE', date_default_timezone_get());

use TimeTraveler\Libraries\Core as TimeTraveler;

$cli = new CLI();

$traveler = new TimeTraveler();
$traveler->setDefaultTimezone(DEFAULT_TIMEZONE);
$traveler->setCLIInstance($cli);

$cli->header("'TimeTraveler' by Chris Rossi");
$cli->subHeader("This CLI tool allows you to calculate the difference between two dates");
$cli->lineBreak();

// Prompt for From Date
$traveler->requestFromDate();

// Prompt for To Date
$traveler->requestToDate();

// Prompt for Output Format
$traveler->requestOutputFormat();

// Render output as Table
$formatteddiff = $traveler->getFormattedDifference();
$daysbetween = $traveler->getDaysBetween();
$weekdaysbetween = $traveler->getWeekdaysBetween();
$weeksbetween = $traveler->getWeeksBetween();

$data = [
    ['Data', 'Value', 'Custom Format'],
    ['From Date', $traveler->getDate('from'), null],
    ['To Date', $traveler->getDate('to'), null],
    ['Difference', $formatteddiff, null],
    ['Difference - Days', $daysbetween, $traveler->convert($daysbetween, 'W')],
    ['Difference - Weekdays', $weekdaysbetween, $traveler->convert($weekdaysbetween, 'D')],
    ['Difference - Complete Weeks', $weeksbetween, $traveler->convert($weeksbetween, 'W')],
];

$cli->table($data);
