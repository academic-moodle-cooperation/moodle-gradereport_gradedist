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
 * Ajax handler for grade distribution chart.
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

$courseid  = required_param('courseid', PARAM_INT);
$gradeitem = optional_param('gradeitem', null, PARAM_INT);
$updateall = optional_param('updateall', false, PARAM_BOOL);

$gradeletters = $_POST['grp_gradeletters'];
$boundaries  = $_POST['grp_gradeboundaries'];
$boundaries_new = $_POST['grp_gradeboundaries_new'];

// Basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manageletters', $context);

$PAGE->set_url('/grade/report/gradedist/gradedist.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');//calling this here to make blocks display

$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'gradedist', 'courseid'=>$course->id));
$returnurl = $gpr->get_return_url('index.php?id='.$course->id);

$data = new stdClass();
$letters = grade_get_letters($context);
$newletters = array();

$i = 1;
foreach ($letters as $letter) {
    $oldletters[$boundaries[$i]] = $letter;
    if (!empty($boundaries_new[$i])) {
        $newletters[$boundaries_new[$i]] = $letter;
    }
    $i++;
}

$grader = new grade_report_gradedist($course->id, $gpr, $context, $letters);

$data->olddist = $grader->load_distribution($oldletters, $gradeitem);
$data->newdist = $grader->load_distribution($newletters, $gradeitem);

$data->courseid = $courseid;
$data->gradeitem = $gradeitem;
$data->updateall = $updateall;

echo json_encode($data);