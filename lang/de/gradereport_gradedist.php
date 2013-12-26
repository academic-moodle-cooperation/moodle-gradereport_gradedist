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
$string['pluginname'] = 'Notenverteilung';

$string['gradedist:view'] = 'Notenverteilung anzeigen';
$string['gradedist:edit'] = 'Notenverteilung bearbeiten';

$string['gradeitem'] = 'Bewertungsspalte';
$string['coursesum'] = 'Summe für den Kurs';

$string['gradeletter'] = 'Notenstufen';

$string['gradeboundary'] = 'Untere Grenze in %';
$string['gradeboundary_help'] = 'Eine prozentuale Grenze über der die Bewertung einer bestimmten Notenstufe zugeordnet wird.';
$string['gradeboundary_new'] = 'Neue untere Grenze in %';
$string['gradeboundary_new_help'] = 'Unter neue untere Grenze wird die Auswirkung dieser auf die Notenverteilung angezeigt. Nur die Eingabe von Dezimalzahlen mit bis zu 2 Nachkommastellen im Bereich 0-100 ist möglich.';

$string['chart'] = 'Diagramm';

$string['description'] = 'Beschreibung';
$string['absolut'] = 'Absolut';
$string['percent'] = 'Prozent';

$string['columns'] = 'Balken';
$string['actualcolumns'] = 'aktuelle Notenstufen';
$string['newcolumns'] = 'neue Notenstufen';

$string['interval'] = 'Sie können als untere Grenze nur Werte zwischen 0-100 eingeben.';
$string['decimals'] = 'Sie können als untere Grenze nur Dezimalzahlen mit bis zu 2 Nachkommastellen eingeben.';
$string['predecessor'] = 'Jede Notenstufe muss eine niedrigere untere Grenze haben als die nächsthöhere Notenstufe.';

$string['changeletters'] = 'Notenstufen ändern';
$string['confirm'] = 'Notenstufen wirklich ändern';

$string['notification'] = 'Hinweis: Die Notenstufen werden für alle Spalten Ihres Kurses übernommen und können nur für den gesamten Kurs gesetzt werden.';

$string['boundaryerror'] = 'Die Eingabe für neue Notenstufen enthält ungültige Werte. Es müssen alle Notenstufen ausgefüllt sein.';
$string['saved'] = 'Die Notenstufen wurden erfolgreich geändert.';