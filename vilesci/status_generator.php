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
/**
 * Generiert die Studierenden Historie anhand der eingetragenen Noten
 *
 * Das Applicant Syncro legt nur Interessent / Bewerber / Abgewiesenen Status an
 * Das Stuierenden Syncro legt nur den 1. Studierendenstatus / Unterbrecher / Absolventen Status an
 *
 * Bei den Vorhandenen Stati ist das Ausbildungssemester (mit ausnahme des Absolventen) nicht korrekt
 * Und muss anhand der eingetragenen Noten korrigiert werden
 *
 * Studierendenstati fuer die einzelnen Semester sind ebenfalls nicht vorhanden. Hier werden die fehlenden
 * Stati anhand der vorhandenen Noten generiert.
 * Zusätzlich wird der Eingtrag in der Tabelle Studentlehrverband gesetzt.
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/studiensemester.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
	<title>LBS Datenmigration - Status Generator</title>
</head>
<body>
<h1>LBS Datenmigration - Status Generator</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$datum = new datum();

if(!$rechte->isBerechtigt('addon/lbsdatenmigration'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$db = new basis_db();
$logfile='status_generator.log';

// Logfile oeffnen
if(!$loghandle=fopen($logfile, 'w'))
	die("Kann Logfile $logfile nicht öffnen!");

logMessage("Starte Datenuebernahme");

$statistik_fehler=0;
$statistik_studierendenstati_neu=0;
$statistik_studierendenstati_fehler=0;
$statistik_studentlehrverband_neu=0;
$statistik_studentlehrverband_fehler=0;

$qry_student = "SELECT * FROM public.tbl_student";

if($result_student = $db->db_query($qry_student))
{
	while($row_student = $db->db_fetch_object($result_student))
	{
		$notensemester=array();

		$qry_notensemester = "
			SELECT
				distinct tbl_lehrveranstaltung.semester, tbl_zeugnisnote.studiensemester_kurzbz
			FROM
				lehre.tbl_zeugnisnote
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE student_uid=".$db->db_add_param($row_student->student_uid).
			" AND tbl_lehrveranstaltung.semester!=0 ORDER BY tbl_lehrveranstaltung.semester";

		if($result_notensemester = $db->db_query($qry_notensemester))
		{
			$idx=0;
			while($row_notensemester = $db->db_fetch_object($result_notensemester))
			{
				$notensemester[$idx]['semester']=$row_notensemester->semester;
				$notensemester[$idx]['studiensemester_kurzbz']=$row_notensemester->studiensemester_kurzbz;
				$idx++;
			}
		}

		if(isset($notensemester[0]))
		{
			// Einstiegssemester

			$qry = "UPDATE public.tbl_prestudentstatus SET ausbildungssemester=".$db->db_add_param($notensemester[0]['semester'])."
					WHERE prestudent_id=".$db->db_add_param($row_student->prestudent_id)."
					AND status_kurzbz IN('Interessent','Bewerber','Student');";
		}

		// Studierendenstati anlegen
		foreach($notensemester as $row_notensemester)
		{
			$prestudent = new prestudent();
			//Pruefen ob bereits ein Stati vorhanden ist
			if($prestudent->load_rolle($row_student->prestudent_id, 'Student', $row_notensemester['studiensemester_kurzbz'], $row_notensemester['semester']))
			{
				// Status ist bereits vorhanden
			}
			else
			{
				$prestudent->prestudent_id = $row_student->prestudent_id;
				$prestudent->status_kurzbz='Student';
				$prestudent->studiensemester_kurzbz=$row_notensemester['studiensemester_kurzbz'];
				$prestudent->ausbildungssemester=$row_notensemester['semester'];
				$prestudent->datum = getDatumStudiensemester($row_notensemester['studiensemester_kurzbz']);
				$prestudent->new=true;
				if($prestudent->save_rolle())
				{
					$statistik_studierendenstati_neu++;
				}
				else
				{
					$statistik_studierendenstati_fehler++;
					logMessage("Fehler beim Anlegen des Stati:".$prestudent->errormsg);
				}
			}

			// Studentlehrverband eintrag anlegen
			$student = new student();
			if($student->studentlehrverband_exists($row_student->prestudent_id, $row_notensemester['studiensemester_kurzbz']))
			{
				// Eintrag bereits vorhanden
			}
			else
			{
				// Neuen Studentlehrverband eintrag anlegen
				$student->uid=$row_student->student_uid;
				$student->studiensemester_kurzbz=$row_notensemester['studiensemester_kurzbz'];
				$student->studiengang_kz=$row_student->studiengang_kz;
				$student->semester=$row_notensemester['semester'];
				$student->new = true;
				if($student->save_studentlehrverband())
				{
					$statistik_studentlehrverband_neu++;
				}
				else
				{
					$statistik_studentlehrverband_fehler++;
					logMessage("Fehler beim Anlegen des Studentlehrverband Eintrages:".$student->errormsg);
				}
			}
		}
	}
}

logMessage("----------------------------------------------------");
logMessage("Allgemeine Fehler: $statistik_fehler");
logMessage("Neue Studierendenstati: $statistik_studierendenstati_neu");
logMessage("Fehler bei Studierendenstati: $statistik_studierendenstati_fehler");
logMessage("Neue Studentlehrverband Eintraege: $statistik_studentlehrverband_neu");
logMessage("Fehler bei Studentlehrverband: $statistik_studentlehrverband_fehler");

fclose($loghandle);

/**
 * Gibt die Meldung aus und Speichert diese ins Logfile
 */
function logMessage($message)
{
	global $loghandle;

	$time = date('Y-m-d H:i:s');
	echo "<br>".$message;
	fputs($loghandle, "\n".$time.' >> '.$message);
}

function getDatumStudiensemester($studiensemester_kurzbz)
{
	$stsem = new studiensemester();
	$stsem->load($studiensemester_kurzbz);
	return $stsem->start;
}
?>
