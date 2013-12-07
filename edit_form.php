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

class edit_form extends moodleform {

    public function definition() {
        $mform      =&$this->_form;
        $num        = $this->_customdata['num'];
        $gradeitems = $this->_customdata['gradeitems'];

        $mform->addElement('header', 'gradedist', get_string('pluginname', 'gradereport_gradedist'));
        
        $mform->addElement('select', 'gradeitem', get_string('gradeitem', 'gradereport_gradedist'), $gradeitems);

        $gradeletters = array();
        $gradeboundaries = array();
        $gradeboundaries_new = array();
        $attributes = array('size' => 2, 'style' => 'margin-right:10px');
        
        for($i=1; $i<$num+1; $i++) {
            $gradeletters[] =& $mform->createElement('text', $i, false, array_merge(array('class' => 'gradeletters', 'disabled'=>'disabled'), $attributes));
            $gradeboundaries[] =& $mform->createElement('text', $i, false, array_merge(array('class' => 'gradeboundaries', 'disabled'=>'disabled'), $attributes));
            $gradeboundaries_new[] =& $mform->createElement('text', $i, false, array_merge(array('class' => 'gradeboundaries_new'), $attributes));
        }
        
        $mform->addGroup($gradeletters, 'grp_gradeletters', get_string('gradeletter', 'gradereport_gradedist'), array(''));
        $mform->setType('grp_gradeletters', PARAM_TEXT);
        $mform->addGroup($gradeboundaries, 'grp_gradeboundaries', get_string('gradeboundary', 'gradereport_gradedist'), array(''));
        $mform->setType('grp_gradeboundaries', PARAM_TEXT);
        $mform->addGroup($gradeboundaries_new, 'grp_gradeboundaries_new', get_string('gradeboundary_new', 'gradereport_gradedist'), array(''));
        $mform->setType('grp_gradeboundaries_new', PARAM_TEXT);

        // hidden params
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
    }
}