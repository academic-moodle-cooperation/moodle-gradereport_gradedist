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
 * Grade distribution tool to show and edit grade letters
 *
 * @package       gradereport_gradedist
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

require_once('../../../config.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/lib.php');

require_once('lib.php');
require_once('edit_form.php');
require_once('confirm_form.php');

require_once('mtablepdf.php');
require_once('export.php');

$courseid = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);
$saved = optional_param('saved', false, PARAM_BOOL);

// Basic access checks.
if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new moodle_exception('invalidcourseid');
}
require_login($course);
$context = context_course::instance($course->id);
if (!has_capability('gradereport/gradedist:view', $context)) {
    throw new moodle_exception('nopermissiontoviewletergrade');
}
$edit = (has_capability('gradereport/gradedist:edit', $context) && has_capability('moodle/grade:manageletters', $context));

$PAGE->set_url('/grade/report/gradedist/index.php', ['id' => $courseid]);
$PAGE->set_pagelayout('standard'); // Calling this here to make blocks display.
$PAGE->requires->jquery();

$highchartssrc = file_exists($CFG->dirroot.'/grade/report/gradedist/js/highcharts.src.js') &&
        file_exists($CFG->dirroot.'/grade/report/gradedist/js/exporting.src.js');
$highchartsmin = file_exists($CFG->dirroot.'/grade/report/gradedist/js/highcharts.js') &&
        file_exists($CFG->dirroot.'/grade/report/gradedist/js/exporting.js');

if ($highchartssrc) {
    $PAGE->requires->js('/grade/report/gradedist/js/highcharts.src.js');
} else if ($highchartsmin) {
    $PAGE->requires->js('/grade/report/gradedist/js/highcharts.js');
}

$letters = grade_get_letters($context);
krsort($letters, SORT_NUMERIC);

$gpr = new grade_plugin_return(['type' => 'report', 'plugin' => 'gradedist', 'courseid' => $course->id]);
$returnurl = $gpr->get_return_url('index.php');
$boundaryerror = false;

$grader = new grade_report_gradedist($course->id, $gpr, $context, $letters);
$gradeitemsunsorted = $grader->get_gradeitems();
usort($gradeitemsunsorted, function($a, $b) {
    return (int)$a->sortorder - (int)$b->sortorder;
});
$gradeitems = [];
foreach ($gradeitemsunsorted as $gi) {
    $gradeitems[$gi->gid] = $gi;
}

$groupmode = groups_get_course_groupmode($course);

$coursegroups = $grader->get_grouplist();

$coursegroupings = $grader->get_groupinglist();

$gradeitem = optional_param('gradeitem',
        (isset($SESSION->gradereport_gradedist_gradeitem)) ? $SESSION->gradereport_gradedist_gradeitem : key($gradeitems),
        PARAM_INT);
$boundariesnew = optional_param_array('grp_gradeboundaries_new',
        (isset($SESSION->gradereport_gradedist_boundariesnew)) ? $SESSION->gradereport_gradedist_boundariesnew : [],
        PARAM_TEXT);

$newletters = empty($boundariesnew) ? $letters : [];

$mdata = new stdClass();
$mdata->gradeitem = $gradeitem;
$i = 1; $max = 100.01;
foreach ($letters as $boundary => $letter) {
    $boundary = format_float($boundary, 2);
    $boundarynew = isset($boundariesnew[$i]) ? $boundariesnew[$i] : $boundary;

    $gradelettername = 'grp_gradeletters['.$i.']';
    $gradeboundaryname = 'grp_gradeboundaries['.$i.']';
    $gradeboundarynewname = 'grp_gradeboundaries_new['.$i.']';

    $mdata->$gradelettername   = $letter;
    $mdata->$gradeboundaryname = $boundary;
    $mdata->$gradeboundarynewname = $boundarynew;

    // Validation fallback if javascript is disabled.
    $boundarynew = str_replace(",", ".", $boundarynew);
    if (isset($boundariesnew[$i])) {
        if ($boundarynew == ''
        ||  $boundarynew > 100
        ||  !preg_match('/^\d+([.,]\d{1,2})?$/', $boundarynew)
        ||  $boundarynew >= $max) {
                $boundaryerror = true;
        } else {
                $newletters[$boundarynew] = $letter;
        }
        $max = $boundarynew;
    }
    $i++;
}

$groupid = 0; $groupingid = 0;
$actdist = $grader->load_distribution($letters, $gradeitem, $groupid, $groupingid);
$newdist = $grader->load_distribution($newletters, $gradeitem, $groupid, $groupingid);

$gsel = $grader->group_selector;

$mform = new edit_letter_form($returnurl, [
            'id' => $course->id,
            'num' => count($letters),
            'edit' => $edit,
            'gradeitems' => $gradeitems,
            'coursegroups' => $coursegroups,
            'coursegroupings' => $coursegroupings,
            'groupmode' => $groupmode,
            'actcoverage' => $actdist->coverage,
            'newcoverage' => $newdist->coverage,
            ],
            'post', '', ['id' => 'letterform']
        );
$mform->set_data($mdata);

if (($data = $mform->get_data()) && isset($data->grp_export) && $data->grp_export) {
    // Export.
    $export = new grade_export_gradedist();
    $exportformat = $data->grp_export;
    switch($exportformat) {
        case 'xlsx':
            $efcode = MTablePDF::OUTPUT_FORMAT_XLSX;
            break;
        case 'ods':
            $efcode = MTablePDF::OUTPUT_FORMAT_ODS;
            break;
        case 'csv':
            $efcode = MTablePDF::OUTPUT_FORMAT_CSV_TAB;
    }

    $gradeitem = new stdClass();
    $gradeitem->id = $data->gradeitem;
    $gradeitem->name = $gradeitems[$data->gradeitem]->name;

    $groupid = isset($data->coursegroup) ? $data->coursegroup : 0;
    $groupingid = isset($data->coursegrouping) ? $data->coursegrouping : 0;

    // Export event.
    \gradereport_gradedist\event\gradedist_downloaded::create(
        ['context' => $context, 'other' => ['url' => $returnurl]])->trigger();

    $export->init($course,
                  $grader,
                  $gradeitem,
                  $letters,
                  $newletters,
                  $efcode,
                  $course->shortname.'_'.$gradeitems[$data->gradeitem]->name.'_'.userdate(time(), '%d-%m-%Y', 99, false),
                  $groupid,
                  $groupingid);
    $export->print_grades();
}

if ($confirm && !$boundaryerror) {

    $letters = grade_get_letters($context);
    krsort($letters, SORT_NUMERIC);

    $cdata = new stdClass();
    $tabledata = [];
    $i = 1; $max = 100;
    foreach ($letters as $letter) {
        $gradeboundarynewname = 'grp_gradeboundaries_new['.$i.']';
        $boundary = str_replace(',', '.', $boundariesnew[$i]);

        $line = [];
        $line[] = format_float($max, 2).' %';
        $line[] = format_float($boundary, 2).' %';
        $line[] = format_string($letter);
        $tabledata[] = $line;
        $max = $boundary - 0.01;

        $cdata->$gradeboundarynewname = $boundary;
        $i++;
    }

    $cform = new confirm_letter_form($returnurl, [
                'id' => $course->id,
                'num' => count($letters),
                'gradeitem' => $gradeitem,
                'tabledata' => $tabledata,
                ]
            );
    $cform->set_data($cdata);

    if ($cform->is_cancelled()) {
        // Cancel.
        $SESSION->gradereport_gradedist_gradeitem = $gradeitem;
        $SESSION->gradereport_gradedist_boundariesnew = $boundariesnew;
        redirect($returnurl);

    } else if ($data = $cform->get_data()) {
        // Save the changes to db.
        $oldids = [];
        if ($records = $DB->get_records('grade_letters', ['contextid' => $context->id], 'lowerboundary ASC', 'id')) {
            $oldids = array_keys($records);
        }

        $i = 1;
        foreach ($letters as $letter) {
            $boundary = str_replace(',', '.', $data->grp_gradeboundaries_new[$i]);

            $record = new stdClass();
            $record->letter        = $letter;
            $record->lowerboundary = $boundary;
            $record->contextid     = $context->id;

            if ($oldid = array_pop($oldids)) {
                $record->id = $oldid;
                $DB->update_record('grade_letters', $record);
            } else {
                $DB->insert_record('grade_letters', $record);
            }
            $i++;
        }

        $returnurl = $gpr->get_return_url('index.php', ['id' => $course->id, 'saved' => true]);

        // New letters submitted event.
        \gradereport_gradedist\event\newletters_submitted::create(
            ['context' => $context, 'other' => ['url' => $returnurl]])->trigger();

        redirect($returnurl);

    } else {
        // Show confirmation table.
        print_grade_page_head($course->id, 'report', 'gradedist', get_string('pluginname', 'gradereport_gradedist'));

        echo $OUTPUT->notification(get_string('notification', 'gradereport_gradedist'), notification::NOTIFY_WARNING);

        $cform->display();
        // Confirmation table event.
        \gradereport_gradedist\event\confirmation_table_viewed::create(
            ['context' => $context, 'other' => ['url' => $returnurl]])->trigger();
    }

} else {
    // Gradedist main view.
    $data = new stdClass();
    $data->courseid = $course->id;
    $data->actdist = $actdist->distribution;
    $data->newdist = $newdist->distribution;
    $data->actcoverage = $actdist->coverage;
    $data->newcoverage = $newdist->coverage;
    $data->letters = array_keys($actdist->distribution);
    $data->title = $gradeitems[$gradeitem]->name;
    $data->highcharts_min = $highchartsmin;
    $data->highcharts_src = $highchartssrc;

    // Start output.
    $params = new stdClass();
    $params->data = $data;
    $PAGE->requires->js_call_amd('gradereport_gradedist/gradedist', 'initializer', [$params]);

    print_grade_page_head($course->id, 'report', 'gradedist', get_string('pluginname', 'gradereport_gradedist'));

    if ($saved) {
        echo $OUTPUT->notification(get_string('saved', 'gradereport_gradedist'), 'notifysuccess');
    } else if ($boundaryerror) {
        echo $OUTPUT->notification(get_string('boundaryerror', 'gradereport_gradedist'));
    }

    // Gradedist settings.
    $mform->display();
    echo '<iframe id="printframe"></iframe>';
    // View event.
    \gradereport_gradedist\event\gradedist_viewed::create(
        ['context' => $context, 'other' => ['url' => $returnurl]])->trigger();
}

echo $OUTPUT->footer();
