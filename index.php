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

require_once('lib.php');
require_once('edit_form.php');
require_once('confirm_form.php');

require_once('mtablepdf.php');
require_once('export.php');

global $SESSION;
$courseid = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);
$saved = optional_param('saved', false, PARAM_BOOL);
$export = optional_param('grp_export[export]', '', PARAM_TEXT);

// Basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);
$context = context_course::instance($course->id);
if (!has_capability('gradereport/gradedist:view', $context) && !has_capability('gradereport/gradedist:edit', $context)) {
    print_error('nopermissiontoviewletergrade');
}
$edit = (has_capability('gradereport/gradedist:edit', $context) && has_capability('moodle/grade:manageletters', $context));

$PAGE->set_url('/grade/report/gradedist/index.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard'); // Calling this here to make blocks display
$PAGE->requires->jquery();
$PAGE->requires->js('/grade/report/gradedist/js/highcharts.src.js');

$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'gradedist', 'courseid'=>$course->id));
$returnurl = $gpr->get_return_url('index.php');
$boundaryerror = false;

$letters = grade_get_letters($context);
krsort($letters, SORT_NUMERIC);
$newletters = array();

$grader = new grade_report_gradedist($course->id, $gpr, $context, $letters);
$gradeitems = $grader->get_gradeitems();
reset($gradeitems);

$gradeitem = optional_param('gradeitem', (isset($SESSION->gradeitem)) ? $SESSION->gradeitem : key($gradeitems), PARAM_INT);
$boundaries_new = optional_param_array('grp_gradeboundaries_new', (isset($SESSION->boundaries_new)) ? $SESSION->boundaries_new : array(), PARAM_TEXT);
$SESSION->gradeitem = $gradeitem;
$SESSION->boundaries_new = null;//$boundaries_new;

$mdata = new stdClass();
$mdata->gradeitem = $gradeitem;
$i = 1; $max = 100.01;
foreach ($letters as $boundary=>$letter) {
    $boundary = format_float($boundary, 2);
    $boundary_new = (isset($boundaries_new[$i])) ? $boundaries_new[$i] : null;
    
    $gradelettername = 'grp_gradeletters['.$i.']';
    $gradeboundaryname = 'grp_gradeboundaries['.$i.']';
    $gradeboundary_newname = 'grp_gradeboundaries_new['.$i.']';

    $mdata->$gradelettername   = $letter;
    $mdata->$gradeboundaryname = $boundary;
    $mdata->$gradeboundary_newname = $boundary_new;
    
    if ($boundary_new == '' || $boundary_new > 100 || !preg_match('/^\d+([.,]\d{1,2})?$/', $boundary_new) || $boundary_new >= $max) {
        $boundaryerror = true;
    } else {
        $newletters[$boundary_new] = $letter;
    }
    $max = $boundary_new;
    $i++;
}

$actdist = $grader->load_distribution($letters, $gradeitem);
$newdist = $grader->load_distribution($newletters, $gradeitem);

$mform = new edit_letter_form($returnurl, array(
            'id'=>$course->id,
            'num'=>count($letters),
            'edit'=>$edit,
            'gradeitems'=>$gradeitems,
            'actcoverage'=>$actdist->coverage,
            'newcoverage'=>$newdist->coverage),
            'post', '', array('id'=>'letterform'));
$mform->set_data($mdata);

if (($data = $mform->get_data()) && isset($data->grp_export['export'])) {
    // Export
    $export = new grade_export_gradedist();
    $exportformat = $data->grp_export['exportformat'];
    
    $gradeitem = new stdClass();
    $gradeitem->id = $data->gradeitem;
    $gradeitem->name = $gradeitems[$data->gradeitem]->name;
    
    $export->init($course,
                  $grader,
                  $gradeitem,
                  $letters,
                  $newletters,
                  $exportformat,
                  $course->shortname.'_'.$gradeitems[$data->gradeitem]->name.'_'.userdate(time(), '%d-%m-%Y', 99, false));
    $export->print_grades();
}

if ($confirm && !$boundaryerror) {
    
    $letters = grade_get_letters($context);
    krsort($letters, SORT_NUMERIC);

    $cdata = new stdClass();
    $tabledata = array();
    $i = 1; $max = 100;
    foreach ($letters as $letter) {
        $gradeboundary_newname = 'grp_gradeboundaries_new['.$i.']';
        $boundary = str_replace(',', '.', $boundaries_new[$i]);
        
        $line = array();
        $line[] = format_float($max, 2).' %';
        $line[] = format_float($boundary, 2).' %';
        $line[] = format_string($letter);
        $tabledata[] = $line;
        $max = $boundary - 0.01;

        $cdata->$gradeboundary_newname = $boundary;
        $i++;
    }

    $cform = new confirm_letter_form($returnurl, array(
                'id'=>$course->id,
                'num'=>count($letters),
                'gradeitem'=>$gradeitem,
                'tabledata'=>$tabledata));
    $cform->set_data($cdata);
        
    if ($cform->is_cancelled()) {
        // Cancel
        redirect($returnurl);

    } else if ($data = $cform->get_data()) {
        // Save the changes to db
        $old_ids = array();
        if ($records = $DB->get_records('grade_letters', array('contextid' => $context->id), 'lowerboundary ASC', 'id')) {
            $old_ids = array_keys($records);
        }

        $i = 1;
        foreach($letters as $letter) {
            $boundary = str_replace(',', '.', $data->grp_gradeboundaries_new[$i]);

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
        // Show confirm table
        print_grade_page_head($course->id, 'report', 'gradedist', get_string('pluginname', 'gradereport_gradedist'));

        echo $OUTPUT->notification(get_string('notification', 'gradereport_gradedist'));
        
        $cform->display();
    }
    
} else {
    // Gradedist main view
    $data = new stdClass();
    $data->courseid = $course->id;
    $data->actdist = $actdist->distribution;
    $data->newdist = $newdist->distribution;
    $data->actcoverage = $actdist->coverage;
    $data->newcoverage = $newdist->coverage;

    // Start output
    $jsmodule = array(
        'name' => 'gradereport_gradedist',
        'fullpath' => '/grade/report/gradedist/js/gradedist.js',
        'requires' => array('io-form'),
        'strings'  => array(array('interval', 'gradereport_gradedist'),
                            array('decimals', 'gradereport_gradedist'),
                            array('predecessor', 'gradereport_gradedist'),
                            array('coverage', 'gradereport_gradedist'),
                            array('absolut', 'gradereport_gradedist'),
                            array('percent', 'gradereport_gradedist'),
                            array('gradeletter', 'gradereport_gradedist')
        ));
    $PAGE->requires->js_init_call('M.gradereport_gradedist.init',
            array($data), true, $jsmodule);

    print_grade_page_head($course->id, 'report', 'gradedist', get_string('pluginname', 'gradereport_gradedist'));
    
    if ($saved) {
        echo $OUTPUT->notification(get_string('saved', 'gradereport_gradedist'), 'notifysuccess');
    } else if($boundaryerror) {
        echo $OUTPUT->notification(get_string('boundaryerror', 'gradereport_gradedist'));
    }
    
    // Gradedist settings
    $mform->display();
}

echo $OUTPUT->footer();