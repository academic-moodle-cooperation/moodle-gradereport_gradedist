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
require_once $CFG->libdir.'/grade/constants.php';

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
    
    public function get_users() {
        if (empty($this->users)) {
            $this->load_users();
        }
        return $this->users;
    }
    
    /**
     * We get gradeitems for select here
     */
    public function get_gradeitems() {
        global $CFG, $DB;
        
        $gradeitems = array();
        $gradetypes = (!empty($CFG->gradedist_showgradeitem)) ? explode(',', $CFG->gradedist_showgradeitem) : array();
        
        foreach($this->gtree->get_items() as $g) {
            if($g->gradetype != GRADE_TYPE_VALUE) continue;
            
            $gradeitem = new stdClass();
            
            if (strcmp($g->itemtype, 'course') == 0) {
                $gradeitem->name = get_string('coursesum', 'gradereport_gradedist');
                $gradeitem->disable = ($g->display != 0 && !in_array($g->display, $gradetypes));
                
                // Little hack to get coursesum in front
                $gradeitems = array_reverse($gradeitems, true);
                $gradeitems[$g->id] = $gradeitem;
                $gradeitems = array_reverse($gradeitems, true);
                continue;
            } else if (strcmp($g->itemtype, 'category') == 0) {
                $gc = $DB->get_record('grade_categories', array('id'=>$g->iteminstance ));
                $gradeitem->name = $gc->fullname;
            } else {
                $gradeitem->name = $g->itemname;
            }
            $gradeitem->disable = ($g->display != 0 && !in_array($g->display, $gradetypes));
            $gradeitems[$g->id] = $gradeitem;
        }
        return $gradeitems;
    }

    /**
     * We supply the letters and gradeitem in this query, and get the distribution
     */
    public function load_distribution($newletters, $gradeitem=0) {
        global $CFG, $DB;
        $this->get_users();
        
        $sql = "SELECT g.*, gi.grademax, gi.grademin
                  FROM {grade_items} gi,
                       {grade_grades} g
                WHERE g.itemid = gi.id AND gi.courseid = :courseid
                AND g.itemid = :gradeitem";
        $params = array('gradeitem'=>$gradeitem, 'courseid'=>$this->courseid);
        
        krsort($this->letters); // Just to be sure
        $userids = array_keys($this->users);
        
        $total = 0;
        $count = 0;
        
        $return = new stdClass();
        $return->distribution = array_fill_keys($this->letters, null);
        $return->coverage = array(0, 0);
        
        foreach($this->letters as $letter) {
            $gradedist = new stdClass();
            $gradedist->count       = 0;
            $gradedist->percentage  = 0;
            $return->distribution[$letter] = $gradedist;
        }
        
        if ($grades = $DB->get_records_sql($sql, $params)) {
            foreach ($grades as $grade) {
                if (in_array($grade->userid, $userids) and array_key_exists($grade->itemid, $this->gtree->get_items())) { // Some items may not be present!!
                    if ($grade->hidden || is_null($grade->finalgrade)) {
                        continue;
                    }
                    $total++;
                    
                    // Calculate gradeletter
                    $letter = $this->get_gradeletter($newletters, $grade);
                    
                    if ($letter != null) {
                        $return->distribution[$letter]->count++;
                        $count++;
                    }
                }
            }
            foreach($return->distribution as $gradedist) {
                $gradedist->percentage = ($total > 0) ? round($gradedist->count * 100 / $total, 2) : 0;
            }
            $return->coverage = array($total - $count, $total, ($total > 0) ? round(($total - $count) * 100 / $total, 2) : 0);
        }
        return $return;
    }
    
    public function get_gradeletter($letters, $grade) {
        // Map to range
        $grademin = (isset($grade->grademin)) ? $grade->grademin : $grade->rawgrademin;
        $grademax = (isset($grade->grademax)) ? $grade->grademax : $grade->rawgrademax;
        $gradeint = $grademax - $grademin;
        $value = ($gradeint != 100 || $grademin != 0) ? ($grade->finalgrade - $grademin) * 100 / $gradeint : $grade->finalgrade;
        
        // Calculate gradeletter
        $value = bounded_number(0, $value, 100); // Just in case
        foreach ($letters as $boundary => $letter) {
            if ($value >= $boundary) {
                return format_string($letter);
            }
        }
    }
}