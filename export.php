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
 * Used as adapter to fill in the data of the gradeletters into the exportclass
 *
 * @package   gradereport_gradedist
 * @copyright 2013 Günther Bernsteiner (guetar@gmx.at)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('mtablepdf.php');

/**
 * @package   gradereport_gradedist
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exportworkbook {

    public function export($course, $gradeitem, $actdist, $newdist, $exportformat, $filename) {
        global $USER;
        
        $export = new MTablePDF(MTablePDF::portrait, array_fill(0, 5, array('mode' => 'Fixed', 'value' => 20)));
        
        // Set document information
        $export->SetCreator('TUWEL');
        $export->SetAuthor($USER->firstname . " " . $USER->lastname);
        $export->setOutputFormat($exportformat);
        
        // Show course in header
        $export->setHeaderText($course->fullname, '',
                               $course->shortname, '',
                               '', '',
                               'gradeitem', '',
                               '', '', '', '');
        
        $export->setTitles(array(
            0 => get_string('category', 'gradereport_gradedist'),
            1 => get_string('actualcolumns', 'gradereport_gradedist').get_string('p', 'gradereport_gradedist'),
            2 => get_string('actualcolumns', 'gradereport_gradedist').get_string('a', 'gradereport_gradedist'),
            3 => get_string('newcolumns', 'gradereport_gradedist').get_string('p', 'gradereport_gradedist'),
            4 => get_string('newcolumns', 'gradereport_gradedist').get_string('a', 'gradereport_gradedist')
        ));
        
        foreach ($actdist->distribution as $letter => $gradedist) {
            $export->addRow(array(
                0 => $letter,
                1 => $actdist->distribution[$letter]->percentage,
                2 => $actdist->distribution[$letter]->count,
                3 => $newdist->distribution[$letter]->percentage,
                4 => $newdist->distribution[$letter]->count
            ));
        }
        $export->addRow(array(
            0 => get_string('sum', 'gradereport_gradedist'),
            1 => 100,
            2 => $actdist->coverage[1],
            3 => 100,
            4 => $newdist->coverage[1]));
        
        $export->generate($filename);
        exit;
    }
}