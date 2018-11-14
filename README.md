# Time Traveler - PHP CLI Date/Time Comparison Tool
## By Chris Rossi

[![CircleCI](https://circleci.com/gh/prodestrian/timetraveler/tree/master.svg?style=svg)](https://circleci.com/gh/prodestrian/timetraveler/tree/master)

## About

'Time Traveler' is a simple PHP-based CLI tool for comparisons between dates/times across one or more timezones.

It also allows the output response to be returned in a custom format (years, days, hours, minutes, seconds).

## Requirements
1. PHP 7.1
2. Composer

## Installation

1. Clone Git Repository
2. Install Dependencies:
    ```bash
    composer install
    ```

## Usage
1. Open a terminal in the root directory of the repository
2. Run:
    ```bash
    php default.php
    ```
3. Follow the prompts

## Running Unit Tests
1. Run PHPUnit from the root directory of the repository:
    ```bash
    # All Unit Tests are stored in the 'tests' directory
    vendor/bin/phpunit tests
    ```
