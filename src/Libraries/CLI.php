<?php

/**
 * Wrapper class for CLImate PHP CLI library
 */
namespace TimeTraveler\Libraries;

use League\CLImate\CLImate;

final class CLI
{
    protected $writer;

    public function __construct()
    {
        $this->writer = new CLImate;
    }

    /**
     * Any methods which have no overrides should be
     * passed directly to CLImate Instance
     */
    public function __call($method, $args)
    {
        return $this->writer->$method($args);
    }

    /**
     * Prompt User for Input with a Validation Callback
     *
     * @param string   $question Question to ask User
     * @param function $callback Callback function to handle validation
     * @param string   $errormsg Error Message in case of failure
     * @return string  Value provided by user
     */
    public function promptWithValidation($question, $callback, $errormsg)
    {
        $value = $this->getInput($question);

        // Call the Callback function to handle validation (returns true/false)
        $response = call_user_func($callback, $value);
        if (!$response) {
            // Validation Failed
            // Replace predefined string with user value, output error message
            $this->writer->error(str_replace('<VALUE>', $value, $errormsg));
            // Restart process
            return $this->promptWithValidation($question, $callback, $errormsg);
        }
        // Validation Succeeded, return user value
        return $value;
    }

    /**
     * Output a 'Header' (actually just a green-bordered table)
     *
     * @param string $text Header Text to display
     */
    public function header($text)
    {
        return $this->writer->greenTable([[$text]]);
    }

    /**
     * Output a 'Sub Header' (cyan text)
     *
     * @param string $text Subheader Text to display
     */
    public function subHeader($text)
    {
        return $this->writer->cyan($text);
    }

    /**
     * Output a Question with an optional Comment below
     * NOTE: Does not prompt user, to be displayed before input prompt
     *
     * @param string $question Question to ask user
     * @param string $comment  Optional Comment to display below Question
     */
    public function question($question, $comment = null)
    {
        $this->writer->bold()->comment($question);
        if ($comment) {
            $this->subComment("\t{$comment}");
        }
    }

    /**
     * Output a 'SubComment' (dimmed text)
     *
     * @param string $text Comment text to display
     */
    public function subComment($text)
    {
        return $this->writer->dim()->out($text);
    }

    /**
     * Add a linebreak to the output
     */
    public function lineBreak()
    {
        return $this->writer->break();
    }

    /**
     * Ask the User for Input (with optional valid responses)
     *
     * @param string $question Question to ask user
     * @param array  $accept   Array containing valid responses
     */
    public function getInput($question, $accept = null)
    {
        $input = $this->writer->bold()->input($question);
        if (is_array($accept) && !empty($accept)) {
            $input->accept($accept);
        }
        // NOTE: trim() may be unnecessary
        // Additional sanitization could be added here if needed
        return trim($input->prompt());
    }

    /**
     * Prompt the user for Confirmation (a yes/no response)
     *
     * @param string $question  Question user will confirm
     */
    public function getConfirmation($question)
    {
        $input = $this->writer->bold()->confirm($question);
        return $input->confirmed();
    }

    /**
     * Output data as a formatted Table
     * As per documentation:
     * https://climate.thephpleague.com/terminal-objects/table/
     *
     * @param array $data Table Data to output
     */
    public function table($data)
    {
        return $this->writer->table($data);
    }
}
