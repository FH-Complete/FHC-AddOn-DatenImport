<?php
/* Copyright (C) 2013 FH Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 */

// Name des Addons
$addon_name = 'Datenimport';

// Versionsnummer des Addons
$addon_version = '0.01';

// FH-Complete Min-Version
$fhcomplete_target_version = '3.0';

$addon_description = '
Addon zur Datenübernahme aus externen Systemen

Die Daten werden aus dem Altsystem als CSV exportiert und im Order ./vilesci/data/ abgelegt.
Profilbilder werden per Script exportiert und im Ordner ./vilesci/data/bilder/ abgelegt.

Die CSV Dateien können über Vilesci in die Datenbank importiert werden.
Und werden von dort in die FH-Complete Tabellen verteilt.

Die Profilbilder können nach der Synchronisierung der Basisdaten importiert werden. 

Anmerkungen zum Export als CSV:
Beim Export der CSV Dateien muss bei Faculty in der Symbolleiste das Augen Icon "Show All Records" vor dem Export angeklickt werden.
Es wird dann Record 1 of 105 (105 total) angezeigt. Ansonsten werden nicht alle in das CSV exportiert.

Das selbe gilt für den Export der Bilders


';
?>
