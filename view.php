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


require_once('../../config.php');
require_once('./lib.php');
require_once('./locallib.php');
require_once('./renderer.php');

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
    throw new moodle_error('missingid', 'mod_bitcode');
}

// Require the user to be loggin in the given course context.
require_login($bitcode->course, true, $bitcode->cm);

//Log that the user has viewed this BitCode exhange.
$bitcode->log_view();

/// Print the page header
$PAGE->set_url('/mod/bitcode/view.php', array('id' => $bitcode->cm->id));
$PAGE->set_title(format_string($bitcode->name));
$PAGE->set_heading(format_string($bitcode->course->fullname));
$PAGE->set_context($bitcode->context);
$PAGE->set_cacheable(false);
$PAGE->add_body_class('bitcode-exchange');
//$PAGE->set_focuscontrol('some-html-id');

// Get the renderer in charge of rendering the given page.
$output = $PAGE->get_renderer('mod_bitcode');

// Output starts here
echo $output->header();
echo $output->heading($bitcode->name);

// If intro text is provided, display it.
if ($bitcode->intro) { 
    echo $output->box(format_module_intro('bitcode', $bitcode, $bitcode->cm->id), '', 'bitcodeintro');
}

//Render the core bitcode exchange.
echo $output->render($bitcode);

// Finish the page
echo $output->footer();
