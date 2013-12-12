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
 * A moodleform for editing grade letters
 *
 * @package   gradereport_gradedist
 * @copyright 2013 Günther Bernsteiner (guetar@gmx.at)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once $CFG->libdir.'/formslib.php';

class confirm_letter_form extends moodleform {
    
    protected function definition() {
        $mform     =&$this->_form;
        $num       = $this->_customdata['num'];
        $tabledata = $this->_customdata['tabledata'];
        
        $table = new html_table();
        $table->head  = array(get_string('max', 'grades'), get_string('min', 'grades'), get_string('letter', 'grades'));
        $table->size  = array('30%', '30%', '40%');
        $table->align = array('left', 'left', 'left');
        $table->width = '30%';
        $table->data  = $tabledata;
        $table->tablealign  = 'center';
        $mform->addElement('html', html_writer::table($table));
        
        $gradeletters = array();
        $gradeboundaries = array();
        $gradeboundaries_new = array();
        $attributes = array('style' => 'display:none;');
        
        for($i=1; $i<$num+1; $i++) {
            $mform->addElement('hidden', 'grp_gradeboundaries_new['.$i.']', '', array_merge(array('class' => 'gradeboundaries_new'), $attributes));
            $mform->setType('grp_gradeboundaries_new['.$i.']', PARAM_TEXT);
        }
        
        // hidden params
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_BOOL);
        
        // buttons
        $this->add_action_buttons(true, get_string('confirm', 'gradereport_gradedist'));
    }
}