<?php

/**
 * Primary entrypoint into the TimeTraveler CLI tool
 */

require_once "vendor/autoload.php";

use TimeTraveler\Libraries\CLI;
use TimeTraveler\Libraries\Core as TimeTraveler;

// Initialise CLI Instance
$cli = new CLI();

// Initialise Core TimeTraveler class with Timezone + CLI instance
$traveler = new TimeTraveler();
$traveler->setDefaultTimezone(date_default_timezone_get());
$traveler->setCLIInstance($cli);

// Add intro message
$cli->header("'TimeTraveler' by Chris Rossi");
$cli->subHeader("This CLI tool allows you to calculate the difference between two dates");
$cli->lineBreak();

// Prompt for 'From' Date
$traveler->requestFromDate();

// Prompt for 'To' Date
$traveler->requestToDate();

// Prompt for Output Format
$traveler->requestOutputFormat();

// Render Output as Table
$formatteddiff = $traveler->getFormattedDifference();
$daysbetween = $traveler->getDaysBetween();
$weekdaysbetween = $traveler->getWeekdaysBetween();
$weeksbetween = $traveler->getWeeksBetween();

$data = [
    ['Data', 'Value', 'Custom Format'],
    ['From Date', $traveler->getDate('from'), null],
    ['To Date', $traveler->getDate('to'), null],
    ['Difference', $formatteddiff, null],
    ['Difference - Days', $daysbetween . ' days', $traveler->convert($daysbetween, 'D')],
    ['Difference - Weekdays', $weekdaysbetween . ' weekdays', $traveler->convert($weekdaysbetween, 'D')],
    ['Difference - Complete Weeks', $weeksbetween . ' weeks', $traveler->convert($weeksbetween, 'W')],
];

$cli->table($data);
