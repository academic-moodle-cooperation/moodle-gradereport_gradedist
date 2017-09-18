@gradereport @gradereport_gradedist
Feature: View gradedist in gradebook
  In order to view the gradedist in the course gradebook
  As a teacher
  I need to navigate to the course gradebook

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
    And I am on "Course 1" course homepage
    And I navigate to "View > Grade distribution" in the course gradebook
    And I log out