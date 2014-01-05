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
 * Ajax handler for grade distribution chart
 *
 * @package   gradereport_gradedist
 * @copyright 2013 Günther Bernsteiner (guetar@gmx.at)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once($CFG->dirroot.'/grade/report/gradedist/lib.php');
require_once($CFG->dirroot.'/grade/report/gradedist/edit_form.php');

$courseid  = required_param('id', PARAM_INT);
$gradeitem = optional_param('gradeitem', null, PARAM_INT);
$updateall = optional_param('updateall', false, PARAM_BOOL);

$gradeletters   = optional_param_array('grp_gradeletters', array(), PARAM_TEXT);
$boundaries     = optional_param_array('grp_gradeboundaries', array(), PARAM_TEXT);
$boundaries_new = optional_param_array('grp_gradeboundaries_new', array(), PARAM_TEXT);

// Basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);
$context = context_course::instance($course->id);
require_capability('gradereport/gradedist:edit', $context);

$PAGE->set_url('/grade/report/gradedist/ajax_handler.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');//calling this here to make blocks display

$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'gradedist', 'courseid'=>$course->id));
$returnurl = $gpr->get_return_url('index.php', array('id'=>$course->id));

$letters = grade_get_letters($context);
$newletters = array();

$i = 1;
foreach ($letters as $letter) {
    if ($boundaries_new[$i] != '') {
        $newletters[$boundaries_new[$i]] = $letter;
    }
    $i++;
}

$grader = new grade_report_gradedist($course->id, $gpr, $context, $letters);
$data   = new stdClass();

$actdist = $grader->load_distribution($letters, $gradeitem);
$newdist = $grader->load_distribution($newletters, $gradeitem);
$data->actdist = $actdist->distribution;
$data->newdist = $newdist->distribution;
$data->actcoverage = $actdist->coverage;
$data->newcoverage = $newdist->coverage;

$data->courseid = $courseid;
$data->gradeitem = $gradeitem;
$data->updateall = $updateall;

echo json_encode($data);