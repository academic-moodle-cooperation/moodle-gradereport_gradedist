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
 * @package       gradereport_gradedist
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/grade/report/grader/lib.php');
require_once($CFG->libdir.'/grade/constants.php');

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
     * Pulls out the userids of the users to be display, and sorts them.
     */
    public function load_users() {
        global $CFG, $DB;

        if (!empty($this->users)) {
            return;
        }

        // Limit to users with a gradeable role.
        list($gradebookrolessql, $gradebookrolesparams) =
                $DB->get_in_or_equal(explode(',', $this->gradebookroles), SQL_PARAMS_NAMED, 'grbr0');

        // Limit to users with an active enrollment.
        list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->context);

        // Fields we need from the user table.
        $userfields = user_picture::fields('u', get_extra_user_fields($this->context));

        // We want to query both the current context and parent contexts.
        list($relatedctxsql, $relatedctxparams) =
                $DB->get_in_or_equal($this->context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

        $sortjoin = $sort = $params = null;

        // If the user has clicked one of the sort asc/desc arrows.
        if (is_numeric($this->sortitemid)) {
            $params = array_merge(array('gitemid' => $this->sortitemid),
                      $gradebookrolesparams, $this->groupwheresql_params, $enrolledparams);

            $sortjoin = "LEFT JOIN {grade_grades} g ON g.userid = u.id AND g.itemid = $this->sortitemid";
            $sort = "g.finalgrade $this->sortorder";

        } else {
            $sortjoin = '';
            switch($this->sortitemid) {
                case 'lastname':
                    $sort = "u.lastname $this->sortorder, u.firstname $this->sortorder";
                    break;
                case 'firstname':
                    $sort = "u.firstname $this->sortorder, u.lastname $this->sortorder";
                    break;
                case 'email':
                    $sort = "u.email $this->sortorder";
                    break;
                case 'idnumber':
                default:
                    $sort = "u.idnumber $this->sortorder";
                    break;
            }

            $params = array_merge($gradebookrolesparams, $this->groupwheresql_params, $enrolledparams, $relatedctxparams);
        }

        $sql = "SELECT $userfields
                  FROM {user} u
                  JOIN ($enrolledsql) je ON je.id = u.id
                       $sortjoin
                  JOIN (
                           SELECT DISTINCT ra.userid
                             FROM {role_assignments} ra
                            WHERE ra.roleid IN ($this->gradebookroles)
                              AND ra.contextid $relatedctxsql
                       ) rainner ON rainner.userid = u.id
                   AND u.deleted = 0
              ORDER BY $sort";

        $this->users = $DB->get_records_sql($sql, $params);

        if (empty($this->users)) {
            $this->userselect = '';
            $this->users = array();
            $this->userselect_params = array();
        } else {
            list($usql, $uparams) = $DB->get_in_or_equal(array_keys($this->users), SQL_PARAMS_NAMED, 'usid0');
            $this->userselect = "AND g.userid $usql";
            $this->userselect_params = $uparams;

            // Add a flag to each user indicating whether their enrolment is active.
            $sql = "SELECT ue.userid
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON e.id = ue.enrolid
                     WHERE ue.userid $usql
                           AND ue.status = :uestatus
                           AND e.status = :estatus
                           AND e.courseid = :courseid
                  GROUP BY ue.userid";
            $coursecontext = $this->context->get_course_context(true);
            $params = array_merge($uparams, array(
                'estatus' => ENROL_INSTANCE_ENABLED,
                'uestatus' => ENROL_USER_ACTIVE,
                'courseid' => $coursecontext->instanceid));
            $useractiveenrolments = $DB->get_records_sql($sql, $params);

            $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
            $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
            $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $coursecontext);
            foreach ($this->users as $user) {
                // If we are showing only active enrolments, then remove suspended users from list.
                if ($showonlyactiveenrol && !array_key_exists($user->id, $useractiveenrolments)) {
                    unset($this->users[$user->id]);
                } else {
                    $this->users[$user->id]->suspendedenrolment = !array_key_exists($user->id, $useractiveenrolments);
                }
            }
        }

        return $this->users;
    }

    /**
     * We get gradeitems for select here.
     */
    public function get_gradeitems() {
        global $CFG, $DB;

        $gradeitems = array();
        $gradetypes = (!empty($CFG->gradedist_showgradeitem)) ? explode(',', $CFG->gradedist_showgradeitem) : array();

        foreach ($this->gtree->get_items() as $g) {
            if ($g->gradetype != GRADE_TYPE_VALUE) {
                continue;
            }

            $gradeitem = new stdClass();

            if (strcmp($g->itemtype, 'course') == 0) {
                $gradeitem->name = get_string('coursesum', 'gradereport_gradedist');
                $gradeitem->disable = ($g->display != 0 && !in_array($g->display, $gradetypes));

                // Small hack to get coursesum in front.
                $gradeitems = array_reverse($gradeitems, true);
                $gradeitems[$g->id] = $gradeitem;
                $gradeitems = array_reverse($gradeitems, true);
                continue;
            } else if (strcmp($g->itemtype, 'category') == 0) {
                $gc = $DB->get_record('grade_categories', array('id' => $g->iteminstance ));
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
     * We get groups for select here.
     */
    public function get_grouplist() {

        $groups = array();
        
        $userid = 0;
        $groupingid = 0;
        $groups = groups_get_all_groups($this->courseid, $userid, $groupingid);

        $allgroup = new StdClass();
        $allgroup->name = get_string('allparticipants');
        $allgroup->id = 0; // todo: check if free for use !?
        
        // hack to put "all groups" in front
        $groups = array_reverse($groups, true);
        $groups[$allgroup->id] = $allgroup;
        $groups = array_reverse($groups, true);

        return $groups;
    }

    public function get_groupinglist() {

        $groupings = array();
        
        $userid = 0;
        $groupingid = 0;
        $groupings = groups_get_all_groupings($this->courseid);

        $nogrouping = new StdClass();
        $nogrouping->name = get_string('nogroupingentry', 'gradereport_gradedist');
        $nogrouping->id = 0; // todo: check if free for use !?
        
        // hack to put "no grouping" in front
        $groupings = array_reverse($groupings, true);
        $groupings[$nogrouping->id] = $nogrouping;
        $groupings = array_reverse($groupings, true);

        return $groupings;
    }

    
    /**
     * We supply the letters and gradeitem in this query, and get the distribution.
     */
    public function load_distribution($newletters, $gradeitem=0, $mygroupid=0, $mygroupingid=0) {
        global $CFG, $DB;

        $this->load_users();
        $selectedusers = array();
        $myuserid = 0; // why have to set this ? and is 0 save?
        //file_put_contents("textfile1", print_r($mygroupingid, true), FILE_APPEND);
        if ($mygroupingid == 0) {
            if ($mygroupid == 0) { // tackle all users
                $selectedusers = $this->users;
            } else { // tackle the users of a single group
                $mygroupusers = groups_get_members($mygroupid);
                $selectedusers = array_intersect_key($mygroupusers, $this->users); 
            }
        } else { // tackle the users of the groups of a grouping
//            $mygroups = groups_get_all_groups($this->courseid, $myuserid, $mygroupingid);
//            $mygroupsusers = array();
//            foreach ($mygroups as $groupid => $mygroup) {
//                $mygroupsusers += groups_get_members($groupid);
//            }
            $mygroupsusers = groups_get_grouping_members($mygroupingid);
            $selectedusers = array_intersect_key($mygroupsusers, $this->users); 
        }

        $userids = array_keys($selectedusers);

        $sql = "SELECT g.*, gi.grademax, gi.grademin
                  FROM {grade_items} gi,
                       {grade_grades} g
                WHERE g.itemid = gi.id AND gi.courseid = :courseid
                AND g.itemid = :gradeitem";
        $params = array('gradeitem' => $gradeitem, 'courseid' => $this->courseid);

        krsort($this->letters); // Just to be sure.
        
        $total = 0;
        $count = 0;

        $return = new stdClass();
        $return->distribution = array_fill_keys($this->letters, null);
        $return->coverage = array(0, 0);

        foreach ($this->letters as $letter) {
            $gradedist = new stdClass();
            $gradedist->count       = 0;
            $gradedist->percentage  = 0;
            $return->distribution[$letter] = $gradedist;
        }

        if ($grades = $DB->get_records_sql($sql, $params)) {
            foreach ($grades as $grade) {
                if (in_array($grade->userid, $userids) && array_key_exists($grade->itemid, $this->gtree->get_items())) {
                    // Some items may not be present!!
                    if (is_null($grade->finalgrade)) {
                        continue;
                    }
                    $total++;

                    // Calculate gradeletter.
                    $letter = $this->get_gradeletter($newletters, $grade);

                    if (array_key_exists($letter, $return->distribution)) {
                        $return->distribution[$letter]->count++;
                        $count++;
                    }
                }
            }
            foreach ($return->distribution as $gradedist) {
                $gradedist->percentage = ($total > 0) ? round($gradedist->count * 100 / $total, 2) : 0;
            }
        }
        $return->coverage = array($total - $count, $total, ($total > 0) ? round(($total - $count) * 100 / $total, 2) : 0);
        return $return;
    }

    public function get_gradeletter($letters, $grade) {
        if (is_null($grade->finalgrade) || !$gradeitem = grade_item::fetch(array('id' => $grade->itemid))) {
            return '-';
        }

        // Map to range.
        $gradeint = $gradeitem->grademax - $gradeitem->grademin;
        $value = ($gradeint != 100 || $gradeitem->grademin != 0) ?
                 ($grade->finalgrade - $gradeitem->grademin) * 100 / $gradeint : $grade->finalgrade;

        // Calculate gradeletter.
        $value = bounded_number(0, $value, 100); // Just in case.
        foreach ($letters as $boundary => $letter) {
            $numboundary = str_replace(',', '.', $boundary);
            if ($value >= $numboundary) {
                return format_string($letter);
            }
        }
    }
}