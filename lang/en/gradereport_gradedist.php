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
 * English lang file
 *
 * @package       gradereport_gradedist
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Günther Bernsteiner
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Grade distribution';
$string['pluginname_help'] = 'This report shows the distribution of grades over a particular grade item depending on the lower bounds defined for the grade letters. The preview functionality allows you to see immediately how changes to the lower boundaries of grade letters affect the grade distribution. If you are satisfied with the new lower boundaries the definitions of the grade letters can be permanently changed.
Note: The definition of grade letters applies to all grade items in the course.';

$string['gradedist:view'] = 'View grade distribution';
$string['gradedist:edit'] = 'Manage grade distribution';

$string['gradeitem'] = 'Grading column';
$string['coursesum'] = 'Sum for the course';

$string['labelgroup'] = 'Focus view on group';

$string['labelgrouping'] = 'Focus view on grouping';
$string['nogroupingentry'] = 'No grouping';

$string['gradeletter'] = 'Grade letters';

$string['gradeboundary'] = 'Lower boundary in %';
$string['gradeboundary_help'] = 'The setting determines the minimum percentage over which grades will be assigned to the grade letter.';
$string['gradeboundary_new'] = 'New lower boundary in %';
$string['gradeboundary_new_help'] = 'You can see the effects of new lower boundaries on the distribution of grades. Lower boundaries have to be numbers with max. 2 decimal points.';

$string['chart'] = 'Chart';

$string['printchart'] = 'Print chart';
$string['downloadpng'] = 'Download PNG image';
$string['downloadjpeg'] = 'Download JPEG image';
$string['downloadpdf'] = 'Download PDF document';
$string['downloadsvg'] = 'Download SVG vector image';
$string['contextbuttontitle'] = 'Chart context menu';

$string['description'] = 'Labelling';
$string['absolut'] = 'Absolute';
$string['percent'] = 'Percent';

$string['columns'] = 'Bars';
$string['actualcolumns'] = 'current grade letters';
$string['newcolumns'] = 'new grade letters';

$string['interval'] = 'The lower boundary has to be in between 0 and 100.';
$string['decimals'] = 'The lower boundary has to be a number from 0 to 100. Maximum two decimal places are allowed.';
$string['predecessor'] = 'The lower boundary of a grade letter has to be smaller than the boundary of his predecessor.';
$string['coverage'] = 'The new grade distribution does not cover all grades!';
$string['coverage_export'] = 'Items not included by letters:';
$string['actcoverage'] = 'Items not included by current letters: ';
$string['newcoverage'] = 'Items not included by new letters: ';

$string['changeletters'] = 'Change grade letters';
$string['confirm'] = 'Change grade letters';

$string['notification'] = 'Note: The definition of grade letters applies to all grade items in the course.';

$string['boundaryerror'] = 'Some entries for new grade letters may be invalid. There must be input on each field';
$string['saved'] = 'Grade letters successfully changed.';

$string['export'] = 'Download data as';
$string['download'] = 'Download';

$string['showgradeitem'] = 'Display grade item';
$string['showgradeitem_description'] = 'Show grade display type as choice for grade item.';

$string['category'] = 'Gradecategory';
$string['a'] = ' (absolute)';
$string['p'] = ' (%)';
$string['sum'] = 'Sum';
$string['actualgrade'] = 'Grade (current)';
$string['newgrade'] = 'Grade (new)';
$string['points'] = 'Points ({$a})';

// Events.
$string['gradedistviewed'] = 'Grade distribution viewed';
$string['gradedistviewed_description'] = 'The user with id {$a->userid} viewed the grade distribution.';

$string['gradedistdownloaded'] = 'Current grade distribution downloaded';
$string['gradedistdownloaded_description'] = 'The user with id {$a->userid} downloaded the current grade distribution.';

$string['confirmationtableviewed'] = 'Confirmation table viewed';
$string['confirmationtableviewed_description'] = 'The user with id {$a->userid} viewed the grade distribution confirmation table.';

$string['newletterssubmitted'] = 'New grade letters submitted';
$string['newletterssubmitted_description'] = 'The user with id {$a->userid} submitted the new grade letters.';