@gradereport @gradereport_gradedist
Feature: See gradedist in gradebook
  In order to see the gradedist in the course gradebook
  As a teacher
  I need to follow the gradebook link of the course

  @javascript
  Scenario: follow the gradebook link of a course
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I should see "Grades"
    And I pause scenario execution
    And I navigate to "Grades" node in "Course administration"
    And I should see "Grade distribution"
    And I log out