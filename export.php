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
 * Adapter to fill in the data from the moodleform into the exportclass
 *
 * @package       gradereport_gradedist
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('mtablepdf.php');

/**
 * @package   gradereport_gradedist
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_export_gradedist {

    private $course;
    private $grader;
    private $gradeitem;
    private $letters;
    private $newletters;
    private $exportformat;
    private $filename;

    public function init($course, $grader, $gradeitem, $letters, $newletters, $exportformat, $filename) {
        $this->course = $course;
        $this->grader = $grader;
        $this->gradeitem = $gradeitem;
        $this->letters = $letters;
        $this->newletters = $newletters;
        $this->exportformat = $exportformat;
        $this->filename = $filename;
    }

    public function print_grades() {
        global $DB, $USER;

        $export = new MTablePDF(MTablePDF::PORTRAIT, array_fill(0, 6, array('mode' => 'Fixed', 'value' => 20)));

        // Set document information.
        $export->SetCreator('TUWEL');
        $export->SetAuthor($USER->firstname . " " . $USER->lastname);
        $export->set_outputformat($this->exportformat);

        // Show course in header.
        $export->set_headertext(get_string('course').':', $this->course->shortname,
                               '', '',
                               get_string('gradeitem', 'gradereport_gradedist').':', $this->gradeitem->name,
                               '', '', '', '', '', '');

        // Gradedist data.
        $export->set_titles(array(
            get_string('category', 'gradereport_gradedist'),
            get_string('actualcolumns', 'gradereport_gradedist').get_string('p', 'gradereport_gradedist'),
            get_string('actualcolumns', 'gradereport_gradedist').get_string('a', 'gradereport_gradedist'),
            get_string('newcolumns', 'gradereport_gradedist').get_string('p', 'gradereport_gradedist'),
            get_string('newcolumns', 'gradereport_gradedist').get_string('a', 'gradereport_gradedist'),
            '' // Fit number of columns.
        ));

        $acttotal = 0;
        $newtotal = 0;
        $actdist = $this->grader->load_distribution($this->letters, $this->gradeitem->id);
        $newdist = $this->grader->load_distribution($this->newletters, $this->gradeitem->id);

        foreach ($actdist->distribution as $letter => $gradedist) {
            $acttotal += $actdist->distribution[$letter]->percentage;
            $newtotal += $newdist->distribution[$letter]->percentage;

            $export->add_row(array(
                $letter,
                number_format($actdist->distribution[$letter]->percentage, 2, ',', ' '),
                $actdist->distribution[$letter]->count,
                number_format($newdist->distribution[$letter]->percentage, 2, ',', ' '),
                $newdist->distribution[$letter]->count,
                ''
            ));
        }
        $export->add_row(array(
            get_string('sum', 'gradereport_gradedist'),
            number_format($acttotal, 2, ',', ' '),
            $actdist->coverage[1] - $actdist->coverage[0],
            number_format($newtotal, 2, ',', ' '),
            $newdist->coverage[1] - $newdist->coverage[0],
            ''
        ));

        $export->add_row(array('', '', '', '', '', ''));

        $export->add_row(array(
            get_string('coverage_export', 'gradereport_gradedist'),
            number_format($actdist->coverage[2], 2, ',', ' '),
            $actdist->coverage[0],
            number_format($newdist->coverage[2], 2, ',', ' '),
            $newdist->coverage[0],
            ''
        ));

        // Student data.
        $export->add_row(array('', '', '', '', '', ''));

        $gradeitem = $DB->get_record('grade_items', array('id' => $this->gradeitem->id));
        $gui = new graded_users_iterator($this->course, array($this->gradeitem->id => $gradeitem));
        $gui->init();

        $export->add_row(array(
            get_string('idnumber'),
            get_string('lastname'),
            get_string('firstname'),
            get_string('actualgrade', 'gradereport_gradedist'),
            get_string('newgrade', 'gradereport_gradedist'),
            get_string('points', 'gradereport_gradedist', number_format($gradeitem->grademax, 2, ',', ' '))
        ));

        while ($userdata = $gui->next_user()) {
            $user  = $userdata->user;
            $grade = $userdata->grades[$this->gradeitem->id];
            $actualgrade = $this->grader->get_gradeletter($this->letters, $grade);
            $newgrade = $this->grader->get_gradeletter($this->newletters, $grade);

            $export->add_row(array(
                $user->idnumber,
                $user->lastname,
                $user->firstname,
                $actualgrade,
                (!is_null($newgrade)) ? $newgrade : '',
                (!is_null($grade->finalgrade)) ? number_format($grade->finalgrade, 2, ',', ' ') : ''
            ));
        }

        $export->generate($this->filename);
        exit;
    }
}