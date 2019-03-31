// This file is part of mod_checkmark for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * config.js
 *
 * @package   gradereport_gradedist
 * @author    Andreas Krieger
 * @copyright 2018 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module gradereport_gradedist/config
  */

define([], function() {
    window.requirejs.config({
        paths: {
            // Enter the paths to your required java-script files.
            "filesaver": M.cfg.wwwroot + '/grade/report/gradedist/js/FileSaver',
            "html2pdf": M.cfg.wwwroot + '/grade/report/gradedist/js/html2pdf.bundle.min',
            "canvas2svg": M.cfg.wwwroot + '/grade/report/gradedist/js/canvas2svg',
            "ChartDataLabels": M.cfg.wwwroot + '/grade/report/gradedist/js/plugins/chartjs-plugin-datalabels',
        },
        shim: {
            // Enter the "names" that will be used to refer to your libraries.
            'filesaver': {exports: 'saveAs'},
            'html2pdf': {exports: 'html2pdf'},
            'canvas2svg': {exports: 'C2S'},
            'ChartDataLabels': {exports: 'Chart'},
        }
    });
});