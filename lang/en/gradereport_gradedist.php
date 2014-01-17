<?php

// This file is an extension of Moodle - http://moodle.org/
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
 * Strings for gradedist
 *
 * @package   gradereport_gradedist
 * @copyright 2013 Günther Bernsteiner (guetar@gmx.at)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Grade distribution';

$string['gradedist:view'] = 'View grade distribution';
$string['gradedist:edit'] = 'Manage grade distribution';

$string['gradeitem'] = 'Grading column';
$string['coursesum'] = 'Sum for the course';

$string['gradeletter'] = 'Grade letters';

$string['gradeboundary'] = 'Lower boundary in %';
$string['gradeboundary_help'] = 'The setting determines the minimum percentage over which grades will be assigned the grade letter.';
$string['gradeboundary_new'] = 'New lower boundary in %';
$string['gradeboundary_new_help'] = 'You can see the effects of new lower boundaries on the distribution of grades. Lower boundaries have to be numbers with max. 2 decimal points.';

$string['chart'] = 'Chart';

$string['description'] = 'Description';
$string['absolut'] = 'Absolute';
$string['percent'] = 'Percent';

$string['columns'] = 'Columns';
$string['actualcolumns'] = 'actual grade letters';
$string['newcolumns'] = 'new grade letters';

$string['interval'] = 'The lower boundary has to be in between 0 and 100.';
$string['decimals'] = 'The lower boundary has to be a floating point number with max 2 decimal places.';
$string['predecessor'] = 'The lower boundary of a grade letter has to be smaller than the boundary of his predecessor.';
$string['coverage'] = 'The new grade distribution does not cover all grades!';

$string['actcoverage'] = 'Actual lower boundary does not include grades: ';
$string['newcoverage'] = 'New lower boundary does not include grades: ';

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