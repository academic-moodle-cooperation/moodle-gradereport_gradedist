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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

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

        $mform->addElement('header', 'gradedist', get_string('pluginname', 'gradereport_gradedist'));
        $mform->addHelpButton('gradedist', 'pluginname', 'gradereport_gradedist');

        $select = $mform->createElement('select', 'gradeitem', get_string('gradeitem', 'gradereport_gradedist'));
        foreach ($gradeitems as $index => $gradeitem) {
            $select->addOption($gradeitem->name, $index, ($gradeitem->disable) ? array( 'disabled' => 'disabled') : null);
        }
        $mform->addElement($select);

        if (($groupmode != NOGROUPS)) {
            $selectgroup = $mform->createElement('select', 'coursegroup', get_string('labelgroup', 'gradereport_gradedist'));
            foreach ($coursegroups as $index => $curgroup) {
                $selectgroup->addOption($curgroup->name, $index, null);
            }
            $mform->addElement($selectgroup);

            $selectgrouping = $mform->createElement('select', 'coursegrouping',
                    get_string('labelgrouping', 'gradereport_gradedist'));
            foreach ($coursegroupings as $index => $curgrouping) {
                $selectgrouping->addOption($curgrouping->name, $index, null);
            }
            $mform->addElement($selectgrouping);
        }
        $gradeletters = array();
        $gradeboundaries = array();
        $gradeboundariesnew = array();
        $attributes = array('style' => 'width:65px;margin-right:10px');

        for ($i = 1; $i < $num + 1; $i++) {
            $gradeletters[] =& $mform->createElement('text', $i, false,
                    array_merge(array('class' => 'gradeletters', 'disabled' => 'disabled'), $attributes));
            $gradeboundaries[] =& $mform->createElement('text', $i, false,
                    array_merge(array('class' => 'gradeboundaries', 'disabled' => 'disabled'), $attributes));
            $gradeboundariesnew[] =& $mform->createElement('text', $i, false,
                    array_merge(array('class' => 'gradeboundaries_new'), $attributes));
        }

        $mform->addGroup($gradeletters, 'grp_gradeletters',
                get_string('gradeletter', 'gradereport_gradedist'), array(''));
        $mform->setType('grp_gradeletters', PARAM_TEXT);
        $mform->addGroup($gradeboundaries, 'grp_gradeboundaries',
                get_string('gradeboundary', 'gradereport_gradedist'), array(''));
        $mform->setType('grp_gradeboundaries', PARAM_TEXT);
        $mform->addHelpButton('grp_gradeboundaries', 'gradeboundary', 'gradereport_gradedist');
        $mform->addGroup($gradeboundariesnew, 'grp_gradeboundaries_new',
                get_string('gradeboundary_new', 'gradereport_gradedist'), array(''));
        $mform->setType('grp_gradeboundaries_new', PARAM_TEXT);
        $mform->addHelpButton('grp_gradeboundaries_new', 'gradeboundary_new', 'gradereport_gradedist');

        $mform->addElement('html', '<div id="boundary_error_container"></div>');

        $mform->addElement('header', 'chart', get_string('chart', 'gradereport_gradedist'));

        $description = array();
        $description[] =& $mform->createElement('radio', 'description', '', get_string('absolut', 'gradereport_gradedist'), false);
        $description[] =& $mform->createElement('radio', 'description', '', get_string('percent', 'gradereport_gradedist'), true);
        $mform->setDefault('description', 0);

        $mform->addGroup($description, 'grp_description', get_string('description', 'gradereport_gradedist'), array(''));

        $columns = array();
        $columns[] =& $mform->createElement('advcheckbox', 'actualcolumns', '',
                get_string('actualcolumns', 'gradereport_gradedist'));
        $columns[] =& $mform->createElement('advcheckbox', 'newcolumns', '',
                get_string('newcolumns', 'gradereport_gradedist'));
        $mform->setDefault('grp_columns[actualcolumns]', true);
        $mform->setDefault('grp_columns[newcolumns]', true);

        $mform->addGroup($columns, 'grp_columns', get_string('columns', 'gradereport_gradedist'), array(''));

        $mform->addElement('html', '<canvas height="100" id="chart_container" class="hey"></canvas>');

        $mform->addElement('html', html_writer::div(get_string('actcoverage', 'gradereport_gradedist')
                .html_writer::span($actcoverage[0].'/'.$actcoverage[1].' ('.$actcoverage[2].'%)', 'actcoverage')));
        $mform->addElement('html', html_writer::div(get_string('newcoverage', 'gradereport_gradedist')
                .html_writer::span($newcoverage[0].'/'.$newcoverage[1].' ('.$newcoverage[2].'%)', 'newcoverage')));

        // Hidden params.
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'confirm', true);
        $mform->setType('confirm', PARAM_BOOL);

        // Buttons.
        if ($edit) {
            $mform->addElement('submit', 'submitbutton', get_string('changeletters', 'gradereport_gradedist'));
        }

        $toimage = array();
        $toimage[] =& $mform->createElement(
            'html',
            '(<a href="#png" class="grgd_png">&nbsp;'.get_string('downloadpng', 'gradereport_gradedist').'&nbsp;</a>| '
        );
        $toimage[] =& $mform->createElement(
            'html',
            '<a href="#jpg" class="grgd_jpg">&nbsp;'.get_string('downloadjpeg', 'gradereport_gradedist').'&nbsp;</a>|'
        );
        $toimage[] =& $mform->createElement(
            'html',
            '<a href="#pdf" class="grgd_pdf">&nbsp;'.get_string('downloadpdf', 'gradereport_gradedist').'&nbsp;</a>)'
        );
        $toimage[] =& $mform->createElement(
            'html',
            '(<a href="#print" class="grgd_print">&nbsp;'.get_string('printchart', 'gradereport_gradedist').'&nbsp;</a>)'
        );
        $mform->setDefault('to_image', 0);
        $mform->addGroup($toimage, 'grp_to_image', get_string('exportasimage', 'gradereport_gradedist'), array(''));

        $export = array();
        $exportformats = array(MTablePDF::OUTPUT_FORMAT_ODS     => 'ods',
                               MTablePDF::OUTPUT_FORMAT_CSV_TAB => 'csv',
                               MTablePDF::OUTPUT_FORMAT_XLSX     => 'xlsx');

        $export[] =& $mform->createElement('select', 'exportformat', '', $exportformats);
        $export[] =& $mform->createElement('submit', 'export', get_string('download', 'gradereport_gradedist'));
        $mform->addGroup($export, 'grp_export', get_string('export', 'gradereport_gradedist'), array(''));
        $mform->setDefault('grp_export[exportformat]', MTablePDF::OUTPUT_FORMAT_XLSX);
    }
}