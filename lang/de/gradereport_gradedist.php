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
$string['pluginname_help'] = 'Mit Hilfe der Notenverteilung können Sie sich über eine Bewertungsspalte die Verteilung der Notenstufen anhand der aktuellen unteren Grenzen anzeigen lassen. Sie können neue untere Grenzen festelegen und erkennen auf einen Blick die mögliche Auswirkungen Ihrer Änderung auf die Verteilung. Sind Sie mit den neuen unteren Grenzen zufrieden können Sie die aktuellen Notenstufen des Kurses mit diesen neuen unteren Grenzen überschreiben.
Hinweis: Die Notenstufen werden für alle Spalten Ihres Kurses übernommen und können nur für den gesamten Kurs gesetzt werden.';

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

$string['description'] = 'Beschriftung';
$string['absolut'] = 'Absolut';
$string['percent'] = 'Prozent';

$string['columns'] = 'Balken';
$string['actualcolumns'] = 'aktuelle Notenstufen';
$string['newcolumns'] = 'neue Notenstufen';

$string['interval'] = 'Sie können als untere Grenze nur Werte zwischen 0-100 eingeben.';
$string['decimals'] = 'Sie können als untere Grenze nur Zahlen von 0 bis 100 eingeben. Dabei sind bis zu zwei Dezimalstellen erlaubt.';
$string['predecessor'] = 'Jede Notenstufe muss eine niedrigere untere Grenze haben als die nächsthöhere Notenstufe.';
$string['coverage'] = 'Die neue Notenverteilung erfasst nicht alle Bewertungen!';
$string['coverage_export'] = 'Durch Notenstufen nicht erfasste Bewertungen:';
$string['actcoverage'] = 'Durch aktuelle Notenstufen nicht erfasste Bewertungen: ';
$string['newcoverage'] = 'Durch neue Notenstufen nicht erfasste Bewertungen: ';

$string['changeletters'] = 'Notenstufen ändern';
$string['confirm'] = 'Notenstufen wirklich ändern';

$string['notification'] = 'Hinweis: Die Notenstufen werden für alle Spalten Ihres Kurses übernommen und können nur für den gesamten Kurs gesetzt werden.';

$string['boundaryerror'] = 'Die Eingabe für neue Notenstufen enthält ungültige Werte. Es müssen alle Notenstufen ausgefüllt sein.';
$string['saved'] = 'Die Notenstufen wurden erfolgreich geändert.';

$string['export'] = 'Information herunterladen als';
$string['download'] = 'Herunterladen';

$string['showgradeitem'] = 'Auswahl Bewertungsspalte';
$string['showgradeitem_description'] = 'Zeige Bewertungsaspekte mit folgenden Bewertungsanzeige-Typen als Auswahlmöglichkeit unter Bewertungsspalte an.';

$string['category'] = 'Notenkategorie';
$string['a'] = ' (absolut)';
$string['p'] = ' (%)';
$string['sum'] = 'Summe';
$string['actualgrade'] = 'Note (aktuell)';
$string['newgrade'] = 'Note (neue)';
$string['points'] = 'Punkte ({$a})';