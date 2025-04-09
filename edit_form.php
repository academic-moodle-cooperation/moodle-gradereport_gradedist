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
 * @package       gradereport_gradedist
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Class edit_letter_form
 * @package       gradereport_gradedist
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_letter_form extends moodleform {

    /**
     * form definition
     *
     * @throws coding_exception
     */
    public function definition() {
        global $CFG;

        $mform            =&$this->_form;
        $id               = $this->_customdata['id'];
        $num              = $this->_customdata['num'];
        $edit             = $this->_customdata['edit'];
        $gradeitems       = $this->_customdata['gradeitems'];
        $coursegroups     = $this->_customdata['coursegroups'];
        $coursegroupings  = $this->_customdata['coursegroupings'];
        $groupmode        = $this->_customdata['groupmode'];
        $actcoverage      = $this->_customdata['actcoverage'];
        $newcoverage      = $this->_customdata['newcoverage'];

        $showgradeitemtypes = (isset($CFG->gradedist_showgradeitemtype)) ? $CFG->gradedist_showgradeitemtype : 0;

        $mform->addElement('header', 'gradedistheader', get_string('gradeletter', 'gradereport_gradedist'));
        $mform->addHelpButton('gradedistheader', 'pluginname', 'gradereport_gradedist');

        $select = $mform->createElement('select', 'gradeitem', get_string('gradeitem', 'gradereport_gradedist'));
        foreach ($gradeitems as $index => $gradeitem) {
            $name = $gradeitem->name;
            // If showgradeitemtype-setting is off property module is empty.
            if ($showgradeitemtypes && $gradeitem->module) {
                $modname = 'mod_'.$gradeitem->module;
                $name .= " (".get_string('pluginname', $modname).")";
            } else if ($showgradeitemtypes && $gradeitem->type == "manual") {
                $name .= " (".get_string('manualitem', 'grades').")";
            } else if ($gradeitem->type == get_string('gradecategory', 'grades')) {
                $name .= " (".get_string('gradecategory', 'grades').")";
            }
            $select->addOption($name, $index, ($gradeitem->disable) ? [ 'disabled' => 'disabled' ] : null);
        }
        $mform->addElement($select);

        if (($groupmode != NOGROUPS)) {
            $selectgrouping = $mform->createElement('select', 'coursegrouping',
                    get_string('labelgrouping', 'gradereport_gradedist'));
            foreach ($coursegroupings as $index => $curgrouping) {
                $selectgrouping->addOption($curgrouping->name, $index, null);
            }
            $mform->addElement($selectgrouping);

            $selectgroup = $mform->createElement('select', 'coursegroup', get_string('labelgroup', 'gradereport_gradedist'));
            foreach ($coursegroups as $index => $curgroup) {
                $selectgroup->addOption($curgroup->name, $index, null);
            }
            $mform->addElement($selectgroup);
        }
        $gradeletters = [];
        $gradeboundaries = [];
        $gradeboundariesnew = [];
        $attributes = ['style' => 'width:65px;margin-right:10px'];

        for ($i = 1; $i < $num + 1; $i++) {
            $gradeletters[] =& $mform->createElement('text', $i, false,
                    array_merge(['class' => 'gradeletters', 'disabled' => 'disabled'], $attributes));
            $gradeboundaries[] =& $mform->createElement('text', $i, false,
                    array_merge(['class' => 'gradeboundaries', 'disabled' => 'disabled'], $attributes));
            $gradeboundariesnew[] =& $mform->createElement('text', $i, false,
                    array_merge(['class' => 'gradeboundaries_new'], $attributes));
        }

        $mform->addGroup($gradeletters, 'grp_gradeletters',
                get_string('gradeletter', 'gradereport_gradedist'), '');
        $mform->setType('grp_gradeletters', PARAM_TEXT);
        $mform->addGroup($gradeboundaries, 'grp_gradeboundaries',
                get_string('gradeboundary', 'gradereport_gradedist'), '');
        $mform->setType('grp_gradeboundaries', PARAM_TEXT);
        $mform->addHelpButton('grp_gradeboundaries', 'gradeboundary', 'gradereport_gradedist');
        $mform->addGroup($gradeboundariesnew, 'grp_gradeboundaries_new',
                get_string('gradeboundary_new', 'gradereport_gradedist'), '');
        $mform->setType('grp_gradeboundaries_new', PARAM_TEXT);
        $mform->addHelpButton('grp_gradeboundaries_new', 'gradeboundary_new', 'gradereport_gradedist');

        $mform->addElement('html', '<div id="boundary_error_container"></div>');

        // Buttons.
        if ($edit) {
            $mform->addElement('submit', 'submitbutton', get_string('changeletters', 'gradereport_gradedist'));
        }

        $mform->addElement('header', 'chartheader', get_string('chart', 'gradereport_gradedist'));

        $description = [];
        $description[] =& $mform->createElement('radio', 'description', '', get_string('absolut', 'gradereport_gradedist'), false);
        $description[] =& $mform->createElement('radio', 'description', '', get_string('percent', 'gradereport_gradedist'), true);
        $mform->setDefault('description', 0);

        $mform->addGroup($description, 'grp_description', get_string('description', 'gradereport_gradedist'), '');

        $columns = [];
        $columns[] =& $mform->createElement('advcheckbox', 'actualcolumns', '',
                get_string('actualcolumns', 'gradereport_gradedist'));
        $columns[] =& $mform->createElement('advcheckbox', 'newcolumns', '',
                get_string('newcolumns', 'gradereport_gradedist'));
        $mform->setDefault('grp_columns[actualcolumns]', true);
        $mform->setDefault('grp_columns[newcolumns]', true);

        $mform->addGroup($columns, 'grp_columns', get_string('columns', 'gradereport_gradedist'), '');

        $mform->addElement('html', '<canvas height="100" id="chart_container" class="hey"></canvas>');

        $mform->addElement('html', html_writer::div(
            html_writer::span(get_string('actcoverage', 'gradereport_gradedist')."&nbsp;", 'coveragetext')
                .html_writer::span($actcoverage[0].'/'.$actcoverage[1].' ('.$actcoverage[2].'%)', 'actcoverage')));
        $mform->addElement('html', html_writer::div(
            html_writer::span(get_string('newcoverage', 'gradereport_gradedist')."&nbsp;", 'coveragetext')
                .html_writer::span($newcoverage[0].'/'.$newcoverage[1].' ('.$newcoverage[2].'%)', 'newcoverage')));

        $mform->addElement('html', '<div>&nbsp;</div>');

        // Hidden params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'confirm', true);
        $mform->setType('confirm', PARAM_BOOL);
        $mform->addElement('hidden', 'grp_export', '');
        $mform->setType('grp_export', PARAM_TEXT);

        $mform->addElement('header', 'downloadheader', get_string('download', 'gradereport_gradedist'));

        $mform->addElement('html', '<div class="grgd">'.
            get_string('exportasimage', 'gradereport_gradedist').'&nbsp;'.
            '&nbsp;<a href="#png" class="grgd_png">'.get_string('downloadpng', 'gradereport_gradedist').'</a>&nbsp;|'.
            '&nbsp;<a href="#jpg" class="grgd_jpg">'.get_string('downloadjpeg', 'gradereport_gradedist').'</a>&nbsp;|'.
            '&nbsp;<a href="#pdf" class="grgd_pdf">'.get_string('downloadpdf', 'gradereport_gradedist').'</a>&nbsp;|'.
            '&nbsp;<a href="#print" class="grgd_print">'.get_string('printchart', 'gradereport_gradedist').'</a>'.
            '</div>'
        );

        $mform->addElement('html', '<div class="grgd">'.
            get_string('export', 'gradereport_gradedist').'&nbsp;'.
            '&nbsp;<a href="#xlsx" class="grgd_xlsx">'.get_string('xlsx', 'gradereport_gradedist').'</a>&nbsp;|'.
            '&nbsp;<a href="#ods" class="grgd_ods">'.get_string('ods', 'gradereport_gradedist').'</a>&nbsp;|'.
            '&nbsp;<a href="#csv" class="grgd_csv">'.get_string('csv', 'gradereport_gradedist').'</a>'.
            '</div>'
        );

        $mform->setExpanded('chartheader');

    }
}
