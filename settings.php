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
 * Settings
 *
 * @package       gradereport_gradedist
 * @author        Günther Bernsteiner
 * @author        Andreas Krieger
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    require_once($CFG->libdir.'/grade/constants.php');
    $displaytypes = array(GRADE_DISPLAY_TYPE_REAL => new lang_string('real', 'grades'),
                           GRADE_DISPLAY_TYPE_PERCENTAGE => new lang_string('percentage', 'grades'),
                           GRADE_DISPLAY_TYPE_LETTER => new lang_string('letter', 'grades'),
                           GRADE_DISPLAY_TYPE_REAL_PERCENTAGE => new lang_string('realpercentage', 'grades'),
                           GRADE_DISPLAY_TYPE_REAL_LETTER => new lang_string('realletter', 'grades'),
                           GRADE_DISPLAY_TYPE_LETTER_REAL => new lang_string('letterreal', 'grades'),
                           GRADE_DISPLAY_TYPE_LETTER_PERCENTAGE => new lang_string('letterpercentage', 'grades'),
                           GRADE_DISPLAY_TYPE_PERCENTAGE_LETTER => new lang_string('percentageletter', 'grades'),
                           GRADE_DISPLAY_TYPE_PERCENTAGE_REAL => new lang_string('percentagereal', 'grades')
                           );
    asort($displaytypes);

    $selection = array_fill_keys(array_keys($displaytypes), true);

    $settings->add(new admin_setting_configmulticheckbox('gradedist_showgradeitem',
        get_string('showgradeitem', 'gradereport_gradedist'),
        get_string('showgradeitem_description', 'gradereport_gradedist'), $selection, $displaytypes));

    $settings->add(new admin_setting_configcheckbox('gradedist_showgradeitemtype', get_string('showgradeitemtype', 'gradereport_gradedist'),
        get_string('showgradeitemtype_help', 'gradereport_gradedist'), 0));

}
