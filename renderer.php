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

class mod_bitcode_renderer extends plugin_renderer_base {


    /**
     * Render the core bitcode exchange.
     * 
     * @param bitcode_exchange $bitcode 
     * @return void
     */
    public function render_bitcode_exchange(bitcode_exchange $bitcode) {
        $this->page->set_focuscontrol('to');
        $output = $this->channel_select($bitcode->fromchannel, $bitcode->tochannel);
        $output .= $this->receiver();
        $output .= $this->transmitter();
        $output .= $this->send_receive_ajax($bitcode->cm->id);
        return $output;
    }

    protected function receiver() {
        $output = html_writer::label(get_string('received', 'mod_bitcode'), 'from');
        $output .= html_writer::tag('div', '', array('id' => 'from'));
        return $output;
    }

    protected function transmitter() {
        $output = html_writer::label(get_string('transmitted', 'mod_bitcode'), 'to');
        $output .= html_writer::tag('textarea', '', array('id' => 'to'));
        return $output;
    }

    protected function send_receive_ajax($cmid) {

        // Create the send/receive Javascript module.
        $jsmodule = array(
            'name'     => 'mod_bitcode',
            'fullpath' => '/mod/bitcode/module.js',
            'requires' => array('node'),
            'strings' => array() 
        );

        //Initialize the send/receive module.
        $this->page->requires->js_init_call('M.mod_bitcode.init', array($cmid), true, $jsmodule);

        return '';
    }



    protected function channel_select($fromchannel = 0, $tochannel = 1) {

        // Add the channel selector inputs:
        $output = html_writer::start_tag('div', array('id' => 'channelselectors'));
    
        // "From channel"
        $output .= html_writer::label(get_string('fromchannel', 'mod_bitcode'), 'fromchannel', true, array('id' => 'fromchannellabel'));
        $output .= html_writer::empty_tag('input', array('id' => 'fromchannel', 'name' => 'fromchannel', 'type' => 'text', 'value' => $fromchannel));

        // "To channel"
        $output .= html_writer::label(get_string('tochannel', 'mod_bitcode'), 'tochannel', true, array('id' => 'tochannellabel'));
        $output .= html_writer::empty_tag('input', array('id' => 'tochannel', 'name' => 'tochannel', 'type' => 'text', 'value' => $tochannel));

        $output .= html_writer::end_tag('div');
        return $output;
    }



}
