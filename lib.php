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
     * Constructor.
     * @param int $courseid
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     * @param int $page The current page being viewed (when report is paged)
     * @param int $sortitemid The id of the grade_item by which to sort the table
     */
    public function __construct($courseid, $gpr, $context, $page=null, $sortitemid=null) {
        parent::__construct($courseid, $gpr, $context, $page, $sortitemid);
    }
    
    /**
     * We get gradeitems for select here
     */
    public function get_gradeitems() {
        $gradeitems = array();
        foreach ($this->gtree->get_items() as $gradeitem) {
            if(empty($gradeitems[0]) && strcmp($gradeitem->itemtype, 'course') == 0) {
                $gradeitems[0] = get_string('coursesum', 'gradereport_gradedist');
                continue;
            }
            $gradeitems[$gradeitem->id] = $gradeitem->itemname;
        }
        ksort ($gradeitems);
        return $gradeitems;
    }
}
?>