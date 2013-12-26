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
require_once($CFG->dirroot.'/grade/report/gradedist/confirm_form.php');

$courseid = required_param('id', PARAM_INT);
$boundaries_new = optional_param_array('grp_gradeboundaries_new', array(), PARAM_TEXT);

$confirm = optional_param('confirm', false, PARAM_BOOL);
$saved = optional_param('saved', false, PARAM_BOOL);

// Basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);
$context = context_course::instance($course->id);
require_capability('gradereport/gradedist:edit', $context);

$PAGE->set_url('/grade/report/gradedist/index.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard'); // Calling this here to make blocks display
$PAGE->requires->jquery();
$PAGE->requires->js('/grade/report/gradedist/js/highcharts.js');

$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'gradedist', 'courseid'=>$course->id));
$returnurl = $gpr->get_return_url('index.php');

$mdata = new stdClass();
$mdata->id = $course->id;
$mdata->confirm = true;

$letters = grade_get_letters($context);
krsort($letters, SORT_NUMERIC);
$boundaryerror = false;

$i = 1; $pre = 100;
foreach ($letters as $boundary=>$letter) {
    $boundary = format_float($boundary, 2);
    $gradelettername = 'grp_gradeletters['.$i.']';
    $gradeboundaryname = 'grp_gradeboundaries['.$i.']';

    $mdata->$gradelettername   = $letter;
    $mdata->$gradeboundaryname = $boundary;
    
    if($confirm) {
        $boundary = $boundaries_new[$i];
        if ($boundary == '' || $boundary > 100 || !preg_match('/^\d+(\.\d{1,2})?$/', $boundary) || $boundary > $pre) {
            $boundaryerror = true;
        }
    }
    $i++;
}

$grader = new grade_report_gradedist($course->id, $gpr, $context, $letters);
$gradeitems = $grader->get_gradeitems($letters);
$mform = new edit_letter_form($returnurl, array('num'=>count($letters), 'gradeitems'=>$gradeitems));
$mform->set_data($mdata);

if ($confirm && !$boundaryerror) {
    $cdata = new stdClass();
    $cdata->id = $course->id;
    $cdata->confirm = true;
    $letters = grade_get_letters($context);
    krsort($letters, SORT_NUMERIC);

    // Build table
    $tabledata = array();
    $i = 1; $max = 100;
    foreach ($letters as $letter) {
        $gradeboundaryname = 'grp_gradeboundaries_new['.$i.']';
        $boundary = $boundaries_new[$i];
        
        $line = array();
        $line[] = format_float($max, 2).' %';
        $line[] = format_float($boundary, 2).' %';
        $line[] = format_string($letter);
        $tabledata[] = $line;
        $max = $boundary - 0.01;

        $cdata->$gradeboundaryname = $boundary;
        $i++;
    }

    $cform = new confirm_letter_form(null, array('num'=>count($letters), 'tabledata'=>$tabledata));
    $cform->set_data($cdata);

    if ($cform->is_cancelled()) {
        // Cancel
        $returnurl = $gpr->get_return_url('index.php', array('id'=>$course->id, 'boundaryerror'=>$boundaryerror));
        redirect($returnurl);

    } else if ($data = $cform->get_data()) {
        // Save the changes to db
        $old_ids = array();
        if ($records = $DB->get_records('grade_letters', array('contextid' => $context->id), 'lowerboundary ASC', 'id')) {
            $old_ids = array_keys($records);
        }

        $i = 1;
        foreach($letters as $letter) {
            $boundary = $data->grp_gradeboundaries_new[$i];

            $record = new stdClass();
            $record->letter        = $letter;
            $record->lowerboundary = $boundary;
            $record->contextid     = $context->id;

            if ($old_id = array_pop($old_ids)) {
                $record->id = $old_id;
                $DB->update_record('grade_letters', $record);
            } else {
                $DB->insert_record('grade_letters', $record);
            }
            $i++;
        }
        
        $returnurl = $gpr->get_return_url('index.php', array('id'=>$course->id, 'saved'=>true));
        redirect($returnurl);
    
    } else {
        // Show confirmation table
        print_grade_page_head($course->id, 'report', 'gradedist', get_string('pluginname', 'gradereport_gradedist'));

        echo $OUTPUT->notification(get_string('notification', 'gradereport_gradedist'));
        $cform->display();

        echo $OUTPUT->footer();
    }
    
} else {
    // Gradedist main view
    $data = new stdClass();
    $data->olddist = $grader->load_distribution($letters);
    $data->newdist = $grader->load_distribution(array());

    // Start output
    $jsmodule = array(
        'name' => 'gradereport_gradedist',
        'fullpath' => '/grade/report/gradedist/js/gradedist.js',
        'requires' => array('io-form'),
        'strings'  => array(array('interval', 'gradereport_gradedist'),
                            array('decimals', 'gradereport_gradedist'),
                            array('predecessor', 'gradereport_gradedist'),
                            array('absolut', 'gradereport_gradedist'),
                            array('percent', 'gradereport_gradedist')
        ));
    $PAGE->requires->js_init_call('M.gradereport_gradedist.init',
            array($data), true, $jsmodule);

    print_grade_page_head($course->id, 'report', 'gradedist', get_string('pluginname', 'gradereport_gradedist'));
    
    if ($boundaryerror)
        echo $OUTPUT->notification(get_string('boundaryerror', 'gradereport_gradedist'));
    
    if ($saved)
        echo $OUTPUT->notification(get_string('saved', 'gradereport_gradedist'), 'notifysuccess');

    // Gradedist settings
    $mform->display();
}

echo $OUTPUT->footer();