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
 * @package       gradereport_gradedist
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/report/gradedist/lib.php');
require_once($CFG->dirroot.'/grade/report/gradedist/edit_form.php');

$courseid  = required_param('id', PARAM_INT);
$gradeitem = optional_param('gradeitem', null, PARAM_INT);
$groupid = optional_param('coursegroup', 0, PARAM_INT);
$groupingid = optional_param('coursegrouping', 0, PARAM_INT);
$updateall = optional_param('updateall', false, PARAM_BOOL);

$gradeletters  = optional_param_array('grp_gradeletters', [], PARAM_TEXT);
$boundaries    = optional_param_array('grp_gradeboundaries', [], PARAM_TEXT);
$boundariesnew = optional_param_array('grp_gradeboundaries_new', [], PARAM_TEXT);

// Basic access checks.
if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new moodle_exception('invalidcourseid');
}
require_login($course);
$context = context_course::instance($course->id);
require_capability('gradereport/gradedist:view', $context);

$PAGE->set_url('/grade/report/gradedist/ajax_handler.php', ['id' => $courseid]);
$PAGE->set_pagelayout('standard'); // Calling this here to make blocks display.

$gpr = new grade_plugin_return(['type' => 'report', 'plugin' => 'gradedist', 'courseid' => $course->id]);
$returnurl = $gpr->get_return_url('index.php', ['id' => $course->id]);

$letters = grade_get_letters($context);
$newletters = [];

$i = 1;
foreach ($letters as $letter) {
    if ($boundariesnew[$i] != '') {
        $newletters[$boundariesnew[$i]] = $letter;
    }
    $i++;
}

$grader = new grade_report_gradedist($course->id, $gpr, $context, $letters);
$data   = new stdClass();

$actdist = $grader->load_distribution($letters, $gradeitem, $groupid, $groupingid);
$newdist = $grader->load_distribution($newletters, $gradeitem, $groupid, $groupingid);
// The grade items are sorted by the id when displayed in the edit letter form.
// We need to sort them here as well to getthe correct item
$gradeitemsunsorted = $grader->get_gradeitems();
usort($gradeitemsunsorted, function($a, $b) {
    return (int)$a->sortorder - (int)$b->sortorder;
});
$gradeitems = [];
foreach ($gradeitemsunsorted as $gi) {
    $gradeitems[$gi->gid] = $gi;
}

$coursegroups = groups_get_all_groups($course->id);
$coursegroupings = groups_get_all_groupings($course->id);
$chartsubtitle = "";
// Depends on "0" as hardcoded and available (ToDo)!
if (($groupingid == 0) && ($groupid != 0)) {
    $chartsubtitle = " - ".$coursegroups[$groupid]->name;
} else if (($groupid == 0) && ($groupingid != 0)) {
    $chartsubtitle = " - ".$coursegroupings[$groupingid]->name;
}

$data->actdist = $actdist->distribution;
$data->newdist = $newdist->distribution;
$data->actcoverage = $actdist->coverage;
$data->newcoverage = $newdist->coverage;
$data->letters = array_keys($actdist->distribution);

$data->courseid = $courseid;
$data->gradeitem = $gradeitem;
$data->title = $gradeitems[$gradeitem]->name;
$data->title .= $chartsubtitle;
$data->updateall = $updateall;

echo json_encode($data);
