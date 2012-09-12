<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Core display for the "BitCode Exchange" activity.
 *
 * Provides a vessel which allows students to exchange strings of 1's and 0's,
 * conceptually similar to a walkie talkie. 
 *
 * @package    mod
 * @subpackage bitcode
 * @copyright  2012 Binghamton University
 * @author     Kyle J. Temkin <ktemkin@binghamton.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
//define('NO_DEBUG_DISPLAY', true);

require_once('../../../config.php');
require_once('../lib.php');
require_once('../locallib.php');
require_once('../renderer.php');

// Get the a handle on the given module.
$cmid = optional_param('id', 0, PARAM_INT); // course_module ID, or
$id  = optional_param('b', 0, PARAM_INT);  // bitcode instance ID (legacy-style)

// Get the represented instance of the bitcode module; or throw an exception if we
// don't have enough information.
if($cmid) {
    $bitcode = bitcode_exchange::from_cmid($cmid);
} else if($id) {
    $bitcode = bitcode_exchange::from_id($id);
} else {
    throw new moodle_exception('missingid', 'mod_bitcode');
}

// Ensure that the user is logged in in the given course context.
require_login($bitcode->course, true, $bitcode->cm);

// Load the data to be transmitted and received.
$tx_channel = optional_param('txchan', -1, PARAM_INT);
$rx_channel = optional_param('rxchan', -1, PARAM_INT);
$tx_message = optional_param('tx', '', PARAM_ALPHANUM);

//If the transmit channel is within the valid range...
//TODO: Abstract to a config option.
if($tx_channel >= 0 && $tx_channel <= 1000)  {

    // Transmit the message.
    $bitcode->transmit($tx_channel, $tx_message);

}

if($rx_channel >= 0) {
    echo $bitcode->receive($rx_channel, get_string('nomessage', 'mod_bitcode'));
}
