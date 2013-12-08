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
 * Grade distribution tool to edit grade letters
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

$courseid = required_param('id', PARAM_INT);

// Basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:manageletters', $context);

$PAGE->set_url('/grade/report/gradedist/gradedist.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');//calling this here to make blocks display
$PAGE->requires->jquery();
$PAGE->requires->js('/grade/report/gradedist/js/highcharts.js');

$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'gradedist', 'courseid'=>$course->id));
$returnurl = $gpr->get_return_url('index.php?id='.$course->id);

$data = new stdClass();
$data->override = $DB->record_exists('grade_letters', array('contextid' => $context->id));

$letters = grade_get_letters($context);
$num = count($letters);

$i = 1;
foreach ($letters as $boundary=>$letter) {
    $gradelettername = 'grp_gradeletters['.$i.']';
    $gradeboundaryname = 'grp_gradeboundaries['.$i.']';

    $data->$gradelettername   = $letter;
    $data->$gradeboundaryname = $boundary;
    $i++;
}

$grader = new grade_report_gradedist($course->id, $gpr, $context, $letters);
$gradeitems = $grader->get_gradeitems($letters);
$mform = new edit_form($returnurl, array('num'=>$num, 'gradeitems'=>$gradeitems));
$mform->set_data($data);

$data = new stdClass();
$data->olddist = $grader->load_distribution($letters);
$data->newdist = $grader->load_distribution(array());
$data->courseid  = $course->id;
$data->gradeitem = 0;

// Start output
$jsmodule = array(
        'name' => 'gradereport_gradedist',
        'fullpath' => '/grade/report/gradedist/js/gradedist.js',
        'requires' => array('io-form'),
        'strings'  => array(array('interval', 'gradereport_gradedist'),
                            array('decimals', 'gradereport_gradedist')));
$PAGE->requires->js_init_call('M.gradereport_gradedist.init',
        array($data), true, $jsmodule);

print_grade_page_head($course->id, 'report', 'gradedist', get_string('pluginname', 'gradereport_gradedist'));

$o = '';

// Gradedist settings.
$o .= $mform->render();

// Chart container
$o .= html_writer::div(null, null, array('id'=>'chart_container'));

echo $o;

echo $OUTPUT->footer();