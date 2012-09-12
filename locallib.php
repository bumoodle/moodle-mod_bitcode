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
 * Internal library of functions for module bitcode
 *
 * All the bitcode specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage bitcode
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class bitcode_exchange implements renderable {

    /**
     * @var stdClass Standard meta-data for a course-module.
     */
    public $cm;

    /**
     * @var stdClass Course records for the owning course.
     */
    public $course;

    /**
     * @var int The instance ID for the current bitcode exchange.
     */
    public $id;

    /**
     * @var string The intro text for the current bitcode exchange.
     */
    public $intro;

    /**
     * @var string The intro format for the current bitcode exchange.
     */
    public $introformat;


    /**
     * @var string The name of the given bitcode instance.
     */
    public $name;

    /**
     * @var context The context which owns the given module.
     */
    public $context;

    /**
     * @var int The default channel which the user will listen on.
     */
    public $fromchannel = 0;

    /**
     * @var int The default channel which the user will send on.
     */
    public $tochannel = 1;

    /**
     * Creates a new bit-code exchange instance.
     * 
     * @param stdClass $cm      The database object for the relevant coursemodule.
     * @param stdClass $course  The course object which owns the current module.
     * @param stdClass $record  The raw database records for the current module.
     * @return bitcode_exchange
     */
    public function __construct($cm, $course, $record) {

        global $USER;

        // Store the fields from the constructor...
        $this->cm = $cm;
        $this->course = $course;

        // ... and extract the relevant data from the database.
        $this->fields_from_record($record);

        // Create a reference to the owning context for this course.
        $this->context = get_context_instance(CONTEXT_MODULE, $this->cm->id);

        // Assume a default "transmit channel" of the user's USERID.
        // TODO: abstract to config
        $this->tochannel = $USER->id;
        
    }

    /**
     * Populates this class's internal fields from a database record.
     * 
     * @param stdClass $record The database-record for this instance.
     * @return void
     */
    private function fields_from_record($record) {

        // Activity ID number.
        $this->id = $record->id;

        // Activity name.
        $this->name = $record->name;

        // Introduction text.
        $this->intro = $record->intro;
        $this->introformat =$record->introformat;
    }


    /**
     * Creates a new bitcode exchange object from the given CMID.
     *  
     * @param int $cmid The course-module ID which references the current instance.
     * @return bitcode_exchange The bit-code exchange object represented by the given cmid.
     */
    public static function from_cmid($cmid) {

        global $DB;

        // Get the three core pieces of information: the course-module object (the generalized module
        // metadata for this instance), the course object, and the database record for this instance.
        $cm         = get_coursemodule_from_id('bitcode', $cmid, 0, false, MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $records  = $DB->get_record('bitcode', array('id' => $cm->instance), '*', MUST_EXIST);

        // Create a new bitcode exchange object from the given information.
        return new self($cm, $course, $records);
    }

    /**
     * Creates a new bitcode exchange object from the given ID.
     *  
     * @param int $cmid The course-module ID which references the current instance.
     * @return bitcode_exchange The bit-code exchange object represented by the given id.
     */
     public static function from_id($id) {

        global $DB;

        // Get the three core pieces of information: the course-module object (the generalized module
        // metadata for this instance), the course object, and the database record for this instance.
        $records  = $DB->get_record('bitcode', array('id' => $n), '*', MUST_EXIST);
        $course     = $DB->get_record('course', array('id' => $records->course), '*', MUST_EXIST);
        $cm         = get_coursemodule_from_instance('bitcode', $records->id, $course->id, false, MUST_EXIST);

        // Create a new bitcode exchange object from the given information.
        return new self($cm, $course, $records);
     }

    /**
     * Logs that the current user has viewed the given BitCode exchange.
     */
    public function log_view() {
        add_to_log($this->course->id, 'bitcode', 'view', 'view.php?id='.$this->cm->id, $this->name, $this->cm->id);
    }


    public function transmit($channel, $message) {

        global $DB;

        // Replace any non-binary characters in the message.
        // TODO: allow other codes besides binary
        $message = preg_replace('|[^01]|', '', $message);

        // Attempt to get an existing message, if one exists.
        $old = $DB->get_record('bitcode_messages', array('bitcodeid' => $this->id, 'channel' => $channel), 'id');

        // If a message already exists for this channel, replace it.
        if($old !== false) {
            $DB->update_record('bitcode_messages', array('id' => $old->id, 'message' => $message));
        }
        // Otherwise, create a new message.
        else {
            $DB->insert_record('bitcode_messages', array('bitcodeid' => $this->id, 'message' => $message, 'channel' => $channel));
        }
    }

    public function receive($channel, $default='') {
        
        global $DB;

        // Attempt to find an existing message on this channel in the database.
        $message = $DB->get_record('bitcode_messages', array('bitcodeid' => $this->id, 'channel' => $channel), 'message');

        // If we weren't able to find an existing message, return the default.
        if($message === false) {
            return $default;
        }
        // Otherwise, return the message.
        else {
            return $message->message;
        }
    }


}
