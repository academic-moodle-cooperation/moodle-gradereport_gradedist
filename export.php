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
 * @author        Andreas Krieger
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
    private $groupid;
    private $groupingid;

    public function init($course, $grader, $gradeitem, $letters, $newletters, $exportformat, $filename, $groupid=0, $groupingid=0) {
        $this->course = $course;
        $this->grader = $grader;
        $this->gradeitem = $gradeitem;
        $this->letters = $letters;
        $this->newletters = $newletters;
        $this->exportformat = $exportformat;
        $this->filename = $filename;
        $this->groupid = $groupid;
        $this->groupingid = $groupingid;
    }

    public function print_grades() {
        global $DB, $USER;

        $export = new MTablePDF(MTablePDF::PORTRAIT, array_fill(0, 6, array('mode' => 'Fixed', 'value' => 20)));

        // Set document information.
        $export->SetCreator('TUWEL');
        $export->SetAuthor($USER->firstname . " " . $USER->lastname);
        $export->setoutputformat($this->exportformat);

        // Create title add on if there is a group or grouping selected
        $titleaddon = "";        
        if (($this->groupingid != 0) || ($this->groupid != 0)) {          
            $coursegroups = groups_get_all_groups($this->course->id);
            $coursegroupings = groups_get_all_groupings($this->course->id);

            if (($this->groupingid == 0) && ($this->groupid != 0)) {
                $titleaddon = " - ".$coursegroups[$this->groupid]->name;
            } else if (($this->groupid == 0) && ($this->groupingid != 0)) {
                $titleaddon = " - ".$coursegroupings[$this->groupingid]->name;
            }
        }

        // Show course, gradeitem and (optionally) group/grouping title add on in header
        $export->setheadertext(get_string('course').':', $this->course->shortname,
                               '', '',
                               get_string('gradeitem', 'gradereport_gradedist').':', $this->gradeitem->name . $titleaddon,
                               '', '', '', '', '', '');
        
        // Set a specific override format for the header title and description if default is not ok
        $headertitleformat = array(
            'size' => 12,
            'bold' => 1,
            'align' => 'left',
            'v_align' => 'vcenter');
        $headerdescformat = array();
        $export->set_headerformat($headertitleformat, $headerdescformat);

        // Gradedist data.
        $export->settitles(array(
            get_string('category', 'gradereport_gradedist'),
            get_string('actualcolumns', 'gradereport_gradedist').get_string('p', 'gradereport_gradedist'),
            get_string('actualcolumns', 'gradereport_gradedist').get_string('a', 'gradereport_gradedist'),
            get_string('newcolumns', 'gradereport_gradedist').get_string('p', 'gradereport_gradedist'),
            get_string('newcolumns', 'gradereport_gradedist').get_string('a', 'gradereport_gradedist'),
            '' // Fit number of columns.
        ));

        $acttotal = 0;
        $newtotal = 0;
        $actdist = $this->grader->load_distribution($this->letters, $this->gradeitem->id, $this->groupid, $this->groupingid);
        $newdist = $this->grader->load_distribution($this->newletters, $this->gradeitem->id, $this->groupid, $this->groupingid);

        foreach ($actdist->distribution as $letter => $gradedist) {
            $acttotal += $actdist->distribution[$letter]->percentage;
            $newtotal += $newdist->distribution[$letter]->percentage;

            $export->addrow(array(
                $letter,
                number_format($actdist->distribution[$letter]->percentage, 2, ',', ' '),
                $actdist->distribution[$letter]->count,
                number_format($newdist->distribution[$letter]->percentage, 2, ',', ' '),
                $newdist->distribution[$letter]->count,
                ''
            ));
        }
        $export->addrow(array(
            get_string('sum', 'gradereport_gradedist'),
            number_format($acttotal, 2, ',', ' '),
            $actdist->coverage[1] - $actdist->coverage[0],
            number_format($newtotal, 2, ',', ' '),
            $newdist->coverage[1] - $newdist->coverage[0],
            ''
        ));

        $export->addrow(array('', '', '', '', '', ''));

        $export->addrow(array(
            get_string('coverage_export', 'gradereport_gradedist'),
            number_format($actdist->coverage[2], 2, ',', ' '),
            $actdist->coverage[0],
            number_format($newdist->coverage[2], 2, ',', ' '),
            $newdist->coverage[0],
            ''
        ));

        // Student data.
        $export->addrow(array('', '', '', '', '', ''));

        $gradeitem = $DB->get_record('grade_items', array('id' => $this->gradeitem->id));
        $gui = new graded_users_iterator($this->course, array($this->gradeitem->id => $gradeitem));
        $gui->init();

        $userdatatitleformat = array('size' => 12,
            'bold' => 1,
            'align' => 'center',
            'bottom' => 1,
            'v_align' => 'vcenter');

        $export->addrow(array(
            array("data" => get_string('idnumber'), "format" => $userdatatitleformat),
            array("data" => get_string('lastname'), "format" => $userdatatitleformat),
            array("data" => get_string('firstname'),"format" => $userdatatitleformat),
            array("data" => get_string('actualgrade', 'gradereport_gradedist'),"format" => $userdatatitleformat),
            array("data" => get_string('newgrade', 'gradereport_gradedist'),"format" => $userdatatitleformat),
            array("data" => get_string('points', 'gradereport_gradedist', number_format($gradeitem->grademax, 2, ',', ' ')), "format" => $userdatatitleformat)
        ));

        // create an array of ids of the groups we want to see
        $selectedgroupids = array();
        if ($this->groupingid != 0) {
           $groupsofgrouping = groups_get_all_groups($this->course->id, 0, $this->groupingid);
           foreach ($groupsofgrouping as $onegroup) {
              array_push($selectedgroupids, $onegroup->id);
           }
        }
        if ($this->groupid != 0) {
            array_push($selectedgroupids, $this->groupid);
        }         
        
        while ($userdata = $gui->next_user()) {
            $user  = $userdata->user;

            // if a group or grouping is selected, print only their users
            $ismemberofagroup = TRUE;
            if (($this->groupingid != 0) || ($this->groupid != 0)) {          
                $ismemberofagroup = FALSE;
                foreach ($selectedgroupids as $currentgroupid) {
                    if (groups_is_member($currentgroupid, $user->id)) {$ismemberofagroup = TRUE;}
                }
            }
            
            if (!$ismemberofagroup) {continue;}
            
            $grade = $userdata->grades[$this->gradeitem->id];
            $actualgrade = $this->grader->get_gradeletter($this->letters, $grade);
            $newgrade = $this->grader->get_gradeletter($this->newletters, $grade);

            $export->addrow(array(
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