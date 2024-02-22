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

namespace gradereport_gradedist;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/grade/report/gradedist/lib.php');
require_once $CFG->dirroot . '/grade/lib.php';

/**
 * Tests for Grade distribution
 *
 * @package    gradereport_gradedist
 * @category   test
 * @author     Clemens Marx
 * @copyright  2024 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers     \gradereport_gradedist\lib
 */
class lib_test extends \advanced_testcase {
    /**
     * report to test
     * @var \grade_report_gradedist $report
     */
    protected $report;
    /**
     * course to test
     * @var \stdClass $course
     */
    protected $course;

    protected function setUp(): void {
        $this->resetAfterTest(true);

        $this->course = $this->getDataGenerator()->create_course();
        $gpr = new \grade_plugin_return();
        $context = \context_course::instance($this->course->id);
        $letters = [1, 2, 3, 4, 5];
        $page = 0;
        $sortitemid = 0;

        $this->report = new \grade_report_gradedist($this->course->id, $gpr, $context, $letters, $page, $sortitemid);
    }

    /**
     * Test grade_report_gradedist constructor
     *
     * 2 Assertions
     */
    public function test_constructor() {
        $noreport = null;

        // Assert: Check if the instance is created.
        $this->assertInstanceOf('grade_report_gradedist', $this->report);
        $this->assertEquals($this->course->id, $this->report->courseid);

        // Assert: Check if the instance is not created.
        $this->assertNull($noreport);
    }

    /**
     * Test grade_report_gradedist::load_users
     *
     * 4 Assertions
     */
    public function test_load_users() {
        // Test no users.
        $this->assertEquals([], $this->report->load_users());
        $this->assertEmpty($this->report->load_users());

        // Add users to the course.
        // Used manual users and not the data generator to make sure the sort order is correct.
        $user1 = ['id' => 1, 'username' => 'user1', 'firstname' => 'fn1', 'lastname' => 'ln1', 'email' => 'user1@example.com'];
        $this->getDataGenerator()->enrol_user($user1['id'], $this->course->id);
        $user2 = ['id' => 2, 'username' => 'user2', 'firstname' => 'fn2', 'lastname' => 'ln2', 'email' => 'user2@example.com'];
        $this->getDataGenerator()->enrol_user($user2['id'], $this->course->id);

        $users = $this->report->load_users();

        // Test 2 users in the course.
        $this->assertEquals(2, count($users));

        // Test 2 users in the course sorted.
        $this->assertEquals([$user1['id'], $user2['id']], array_keys($users));
    }

    /**
     * Test grade_report_gradedist::get_gradeitems
     *
     * 2 Assertions
     */
    public function test_get_gradeitems() {
        // Test only 1 grade item (course sum).
        $result = $this->report->get_gradeitems();
        $this->assertEquals(1, count($result));

        $gradeitem1 = $this->getDataGenerator()->create_grade_item([
            'courseid' => $this->course->id,
            'itemtype' => 'manual',
            'itemname' => 'Test grade item 1',
            'sortorder' => 1,
            'gradetype' => 1,
        ]);
        $gradeitem2 = $this->getDataGenerator()->create_grade_item([
            'courseid' => $this->course->id,
            'itemtype' => 'manual',
            'itemname' => 'Test grade item 2',
            'sortorder' => 1,
            'gradetype' => 1,
        ]);

        // Reinstantiate the report to update gtree.
        $this->report = new \grade_report_gradedist(
            $this->course->id,
            new \grade_plugin_return(),
            \context_course::instance($this->course->id),
            [1, 2, 3, 4, 5],
            0,
            0
        );
        $result = $this->report->get_gradeitems();

        // Test 3 grade items (course sum and 2 manual items).
        $this->assertEquals(3, count($result));
    }

    /**
     * Test grade_report_gradedist::get_grouplist
     *
     * 4 Assertions
     */
    public function test_get_grouplist() {
        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Set courseid in the object under test.
        $this->report->courseid = $course->id;

        // Create groups for the course.
        $group1 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $group2 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);

        $result = $this->report->get_grouplist();

        // Assert that $result is as expected.
        $this->assertCount(3, $result); // Assert that there are 3 groups (2 created + 1 for all participants).

        // Assert that the groups in $result match the groups we created.
        $this->assertEquals($group1->id, $result[$group1->id]->id);
        $this->assertEquals($group2->id, $result[$group2->id]->id);
        $this->assertEquals(0, $result[0]->id); // This is the "all participants" group.
    }

    /**
     * Test grade_report_gradedist::get_groupinglist
     *
     * 3 Assertions
     */
    public function test_get_groupinglist() {
        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Set courseid in the object under test.
        $this->report->courseid = $course->id;

        // Create groupings for the course.
        $grouping1 = $this->getDataGenerator()->create_grouping(['courseid' => $course->id]);
        $grouping2 = $this->getDataGenerator()->create_grouping(['courseid' => $course->id]);

        $result = $this->report->get_groupinglist();

        // Assert that $result is as expected.
        $this->assertCount(3, $result); // Assert that there are 3 groupings (2 created + 1 for no grouping).

        // Assert that the groupings in $result match the groupings we created.
        $this->assertEquals($grouping1->id, $result[$grouping1->id]->id);
        $this->assertEquals($grouping2->id, $result[$grouping2->id]->id);
    }

    /**
     * Test grade_report_gradedist::load_distribution
     *
     * 6 Assertions
     */
    public function test_load_distribution() {
        // Test no distribution.
        $letters = [
            90 => 1,
            80 => 2,
            70 => 3,
            50 => 4,
            0 => 5,
        ];

        // Test no entries.
        $result = $this->report->load_distribution($letters);
        $this->assertEquals([0, 0, 0], $result->coverage);

        // Add users to the course.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $this->course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $this->course->id);
        $this->getDataGenerator()->enrol_user($user3->id, $this->course->id);

        // Item to grade.
        $gradeitem1 = $this->getDataGenerator()->create_grade_item([
            'courseid' => $this->course->id,
            'itemtype' => 'manual',
            'itemname' => 'Test grade item 1',
            'sortorder' => 1,
        ]);

        // Grade the users.
        $grade = new \grade_grade([
            'itemid' => $gradeitem1->id,
            'userid' => $user1->id,
            'finalgrade' => 24,
        ], false);
        $grade->insert();
        $grade = new \grade_grade([
            'itemid' => $gradeitem1->id,
            'userid' => $user2->id,
            'finalgrade' => 60,
        ], false);
        $grade->insert();
        $grade = new \grade_grade([
            'itemid' => $gradeitem1->id,
            'userid' => $user3->id,
            'finalgrade' => 85,
        ], false);
        $grade->insert();

        // Reinstantiate the report to update gtree.
        $this->report = new \grade_report_gradedist(
            $this->course->id,
            new \grade_plugin_return(),
            \context_course::instance($this->course->id),
            [1, 2, 3, 4, 5],
            0,
            0
        );

        $result = $this->report->load_distribution($letters, $gradeitem1->id);
        // Used for debugging (@code): print_r($result);.

        // Check coverage (items not included by new letters).
        $this->assertEquals([0, 3, 0], $result->coverage);

        // Check distribution (number of items per grade).
        $this->assertEquals(0, $result->distribution[1]->count);
        $this->assertEquals(1, $result->distribution[2]->count);
        $this->assertEquals(0, $result->distribution[3]->count);
        $this->assertEquals(1, $result->distribution[4]->count);
        $this->assertEquals(1, $result->distribution[5]->count);
    }

    /**
     * Test grade_report_gradedist::get_gradeletter
     *
     * 4 Assertions
     */
    public function test_get_gradeletter() {
        $letters = [
            90 => 1,
            80 => 2,
            70 => 3,
            50 => 4,
            0 => 5,
        ];

        $gradeitem1 = $this->getDataGenerator()->create_grade_item([
            'courseid' => $this->course->id,
        ]);

        $grade1 = new \stdClass();
        $grade1->finalgrade = null;
        $grade1->itemid = $gradeitem1->id;
        $grade1->userid = 1;

        // Test finalgrade is null.
        $this->assertEquals('-', $this->report->get_gradeletter($letters, $grade1));

        // Test finalgrade is 5.
        $grade1->finalgrade = 24;
        $this->assertEquals('5', $this->report->get_gradeletter($letters, $grade1));

        // Test finalgrade is 1.
        $grade1->finalgrade = 100;
        $this->assertEquals('1', $this->report->get_gradeletter($letters, $grade1));

        // Test wrong/no item id.
        $grade1->itemid = null;
        $this->assertEquals('-', $this->report->get_gradeletter($letters, $grade1));
    }
}
