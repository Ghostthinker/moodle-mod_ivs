<?php

namespace mod_ivs\exception;
defined('MOODLE_INTERNAL') || die();

/**
 * Parent fro all deubreak exceptions
 *
 * @package ivs\local\exception
 */
class ivs_exception extends \moodle_exception {

    /**
     * Constructor
     *
     * @param string $errorcode The name of the string to print.
     * @param string $link The url where the user will be prompted to continue.
     *                  If no url is provided the user will be directed to the site index page.
     * @param mixed $a Extra words and phrases that might be required in the error string.
     * @param string $debuginfo optional debugging information.
     */
    public function __construct($errorcode, $link = '', $a = null, $debuginfo = null) {
        parent::__construct($errorcode, 'ivs', $link, $a, $debuginfo);
    }
}
