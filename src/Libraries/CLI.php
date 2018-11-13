<?php

namespace TimeTraveler\Libraries;

use DateTimeZone;
use League\CLImate\CLImate;

final class CLI
{
    protected $writer;
    protected static $validtimezones = [];

    public function __construct()
    {
        $this->writer = new CLImate;
        self::$validtimezones = DateTimeZone::listIdentifiers();
    }

    public function __call($method, $args)
    {
        return $this->writer->$method($args);
    }

    public function promptForTimezone($question)
    {
        $timezone = $this->getInput($question);
        // TODO: Break this out into a passable callback parameter
        // so we can remove all references to timezones from the CLI class
        if (in_array($timezone, self::$validtimezones)) {
            return $timezone;
        }
        $this->writer->error("'" . $timezone . "' is not a valid Timezone, please try again");
        return $this->promptForTimezone($question);
    }

    public function header($text)
    {
        return $this->writer->greenTable([[$text]]);
    }

    public function subHeader($text)
    {
        return $this->writer->cyan($text);
    }

    public function question($question, $comment = null)
    {
        $this->writer->bold()->comment($question);
        if ($comment) {
            $this->subComment("\t{$comment}");
        }
    }

    public function subComment($text)
    {
        return $this->writer->dim()->out($text);
    }

    public function lineBreak()
    {
        return $this->writer->break();
    }

    public function getInput($question, $accept = null)
    {
        $input = $this->writer->bold()->input($question);
        if (is_array($accept) && !empty($accept)) {
            $input->accept($accept);
        }
        return trim($input->prompt());
    }

    public function getConfirmation($question)
    {
        $input = $this->writer->bold()->confirm($question);
        return $input->confirmed();
    }

    public function table($data)
    {
        return $this->writer->table($data);
    }
}
