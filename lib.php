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
 * Definition of the report gradedist class
 *
 * @package   gradereport gradedist
 * @copyright 2013 Günther Bernsteiner (guetar@gmx.at)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/grade/report/grader/lib.php');

/**
 * Class providing an API for the overview report building and displaying.
 * @uses grade_report
 * @package gradereport_overview
 */
class grade_report_gradedist extends grade_report_grader {

    /**
     * The grade letters
     * @var array $letters
     */
    private $letters;
    
    /**
     * Constructor.
     * @param int $courseid
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     * @param array $letters
     * @param int $page The current page being viewed (when report is paged)
     * @param int $sortitemid The id of the grade_item by which to sort the table
     */
    public function __construct($courseid, $gpr, $context, $letters, $page=null, $sortitemid=null) {
        parent::__construct($courseid, $gpr, $context, $page, $sortitemid);
        
        $this->letters = $letters;
    }
    
    /**
     * We get gradeitems for select here
     */
    public function get_gradeitems() {
        global $CFG;
        
        $gradeitems = array();
        $gradetypes = (!empty($CFG->gradedist_showgradeitem)) ? explode(',', $CFG->gradedist_showgradeitem) : array();
        
        foreach($this->gtree->get_items() as $g) {
            if (empty($gradeitems[0]) && strcmp($g->itemtype, 'course') == 0) {
                $gradeitem = new stdClass();
                $gradeitem->name = get_string('coursesum', 'gradereport_gradedist');
                $gradeitem->disable = false;
                $gradeitems[0] = $gradeitem;
                continue;
            }
            $gradeitem = new stdClass();
            $gradeitem->name = $g->itemname;
            $gradeitem->disable = (!in_array($g->display, $gradetypes));
            $gradeitems[$g->id] = $gradeitem;
        }
        ksort ($gradeitems);
        return $gradeitems;
    }

    /**
     * We supply the letters and gradeitem in this query, and get the distribution
     */
    public function load_distribution($newletters, $gradeitem=0) {
        global $CFG, $DB;

        if (empty($this->users)) {
            $this->load_users();
        }
        
        // please note that we must fetch all grade_grades fields if we want to construct grade_grade object from it!
        $wheresql = "";
        $params = array_merge(array('courseid'=>$this->courseid), $this->userselect_params);
        if ($gradeitem != 0) {
            $wheresql = " AND g.itemid = :gradeitem";
            $params['gradeitem'] = $gradeitem;
        }
        $sql = "SELECT g.*, gi.grademax, gi.grademin
                  FROM {grade_items} gi,
                       {grade_grades} g
                 WHERE g.itemid = gi.id AND gi.courseid = :courseid"
                .$wheresql;
        
        krsort($this->letters); // Just to be sure
        $userids = array_keys($this->users);
        
        $total = 0;
        $count = 0;
        
        $return = new stdClass();
        $return->distribution = array_fill_keys($this->letters, null);
        
        foreach($this->letters as $letter) {
            $gradedist = new stdClass();
            $gradedist->count       = 0;
            $gradedist->percentage  = 0;
            $return->distribution[$letter] = $gradedist;
        }
        
        if ($grades = $DB->get_records_sql($sql, $params)) {
            foreach ($grades as $graderec) {
                if (in_array($graderec->userid, $userids) and array_key_exists($graderec->itemid, $this->gtree->get_items())) { // some items may not be present!!
                    if ($graderec->hidden || is_null($graderec->finalgrade)) {
                        continue;
                    }
                    $total++;
                    
                    // Map to percentage
                    $gradeint = $graderec->grademax - $graderec->grademin;
                    if ($gradeint != 100 || $graderec->grademin != 0) {
                        $grade = ($graderec->finalgrade - $graderec->grademin) * 100 / $gradeint;
                    } else {
                        $grade = $graderec->finalgrade;
                    }
                    
                    // Calculate gradeletter
                    reset($newletters);
                    $letter = current($newletters);
                    while ($grade < key($newletters)) {
                        $letter = next($newletters);
                    }
                    if ($letter !== false) {
                        $return->distribution[$letter]->count++;
                        $count++;
                    }
                }
            }
            if ($total > 0) {
                foreach($return->distribution as $gradedist) {
                    $gradedist->percentage = round($gradedist->count * 100 / $total, 2);
                }
            }
            $return->coverage = array($count, $total);
        }
        return $return;
    }
}