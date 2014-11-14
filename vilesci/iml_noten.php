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
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehrfach.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/pruefung.class.php');
require_once('../../../include/zeugnisnote.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
	<title>LBS Datenmigration - IML Noten</title>
</head>
<body>
<h1>LBS Datenmigration - IML Noten</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$datum = new datum();

if(!$rechte->isBerechtigt('addon/lbsdatenmigration'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$db = new basis_db();
$logfile='iml_noten_sync.log';

// Logfile oeffnen
if(!$loghandle=fopen($logfile, 'w'))
	die("Kann Logfile $logfile nicht öffnen!");

logMessage("Starte Datenuebernahme");

$semester=array(
//IBA
'44391-533424183'=>array('ausbsem'=>'1','stsem'=>'WS2007'),
'44412-390122101'=>array('ausbsem'=>'2','stsem'=>'SS2008'),
'36632-252313761'=>array('ausbsem'=>'3','stsem'=>'WS2008'),
'50902-744114396'=>array('ausbsem'=>'4','stsem'=>'SS2009'),
'50937-008773266'=>array('ausbsem'=>'5','stsem'=>'WS2009'),
'50978-655782420'=>array('ausbsem'=>'6','stsem'=>'SS2010'),
'43160-177141072'=>array('ausbsem'=>'1','stsem'=>'WS2008'),
'41500-671198169'=>array('ausbsem'=>'2','stsem'=>'SS2009'),
'45229-681876672'=>array('ausbsem'=>'3','stsem'=>'WS2009'),
'43667-522658535'=>array('ausbsem'=>'1','stsem'=>'WS2009'),
'39020-029340201'=>array('ausbsem'=>'2','stsem'=>'SS2010'),
'41544-636174279'=>array('ausbsem'=>'4','stsem'=>'SS2010'),
'33300-111232157'=>array('ausbsem'=>'1','stsem'=>'WS2010'),
'44962-763971978'=>array('ausbsem'=>'5','stsem'=>'WS2010'),
'45037-945967319'=>array('ausbsem'=>'3','stsem'=>'WS2010'),
'50328-687306933'=>array('ausbsem'=>'2','stsem'=>'SS2011'),
'50540-383102690'=>array('ausbsem'=>'4','stsem'=>'SS2011'),
'50611-554134957'=>array('ausbsem'=>'6','stsem'=>'SS2011'),
'37381-632969494'=>array('ausbsem'=>'1','stsem'=>'WS2011'),
'37624-843764786'=>array('ausbsem'=>'3','stsem'=>'WS2011'),
'37715-228103828'=>array('ausbsem'=>'5','stsem'=>'WS2011'),
'43869-868329733'=>array('ausbsem'=>'2','stsem'=>'SS2012'),
'34868-036658050'=>array('ausbsem'=>'4','stsem'=>'SS2012'),
'34968-728238790'=>array('ausbsem'=>'6','stsem'=>'SS2012'),
'40846-271954826'=>array('ausbsem'=>'3','stsem'=>'WS2012'),
'41353-585414082'=>array('ausbsem'=>'5','stsem'=>'WS2012'),
'51773-682957799'=>array('ausbsem'=>'1','stsem'=>'WS2012'),
'66303-148007683'=>array('ausbsem'=>'2','stsem'=>'SS2013'),    
'66510-184837292'=>array('ausbsem'=>'4','stsem'=>'SS2013'),    
'50513-916603865'=>array('ausbsem'=>'1','stsem'=>'WS2013'),
'48844-582107017'=>array('ausbsem'=>'3','stsem'=>'WS2013'),  
'52696-027100040'=>array('ausbsem'=>'5','stsem'=>'WS2013'),      
//IML
'44305-237631527'=>array('ausbsem'=>'1','stsem'=>'WS2007'),
'44359-106878473'=>array('ausbsem'=>'2','stsem'=>'SS2008'),
'48101-585886533'=>array('ausbsem'=>'3','stsem'=>'WS2008'),
'48130-034219183'=>array('ausbsem'=>'4','stsem'=>'SS2009'),
'45369-430514725'=>array('ausbsem'=>'1','stsem'=>'WS2008'),
'45238-360814428'=>array('ausbsem'=>'2','stsem'=>'SS2009'),
'57792-996743276'=>array('ausbsem'=>'3','stsem'=>'WS2009'),
'57862-623755856'=>array('ausbsem'=>'1','stsem'=>'WS2009'),
'50662-323578521'=>array('ausbsem'=>'2','stsem'=>'SS2010'),
'50799-290123567'=>array('ausbsem'=>'4','stsem'=>'SS2010'),
'35619-159659239'=>array('ausbsem'=>'1','stsem'=>'WS2010'),
'44715-408537361'=>array('ausbsem'=>'3','stsem'=>'WS2010'),
'51317-777727756'=>array('ausbsem'=>'2','stsem'=>'SS2011'),
'51487-331683994'=>array('ausbsem'=>'4','stsem'=>'SS2011'),
'41413-630247611'=>array('ausbsem'=>'1','stsem'=>'WS2011'),
'41467-476233381'=>array('ausbsem'=>'3','stsem'=>'WS2011'),
'35271-576189801'=>array('ausbsem'=>'2','stsem'=>'SS2012'),
'35340-247089915'=>array('ausbsem'=>'4','stsem'=>'SS2012'),
'42446-536968343'=>array('ausbsem'=>'1','stsem'=>'WS2012'),
'42743-110655004'=>array('ausbsem'=>'3','stsem'=>'WS2012'),
'41846-428277947'=>array('ausbsem'=>'2','stsem'=>'SS2013'),    
'41927-513769756'=>array('ausbsem'=>'4','stsem'=>'SS2013'),  
'48525-840995707'=>array('ausbsem'=>'3','stsem'=>'WS2013'),      
//IMM
'84557-398823888'=>array('ausbsem'=>'1','stsem'=>'WS2005'),
'84876-523691679'=>array('ausbsem'=>'3','stsem'=>'WS2005'),
'85056-701273387'=>array('ausbsem'=>'5','stsem'=>'WS2005'),
'85137-773733766'=>array('ausbsem'=>'2','stsem'=>'SS2005'),
'85279-097487604'=>array('ausbsem'=>'4','stsem'=>'SS2005'),
'85328-707628228'=>array('ausbsem'=>'6','stsem'=>'SS2006'),
'59382-485219292'=>array('ausbsem'=>'2','stsem'=>'SS2006'),
'60033-258198888'=>array('ausbsem'=>'4','stsem'=>'SS2006'),
'60236-945566679'=>array('ausbsem'=>'1','stsem'=>'WS2003'),
'60292-435648387'=>array('ausbsem'=>'2','stsem'=>'SS2004'),
'60327-570608766'=>array('ausbsem'=>'1','stsem'=>'WS2004'),
'60476-706862605'=>array('ausbsem'=>'3','stsem'=>'WS2004'),
'49393-542604423'=>array('ausbsem'=>'1','stsem'=>'WS2006'),
'49543-927225820'=>array('ausbsem'=>'3','stsem'=>'WS2006'),
'49622-505824041'=>array('ausbsem'=>'5','stsem'=>'WS2006'),
'49693-893235910'=>array('ausbsem'=>'7','stsem'=>'WS2006'),
'50789-474122784'=>array('ausbsem'=>'2','stsem'=>'SS2007'),
'46123-102259055'=>array('ausbsem'=>'4','stsem'=>'SS2007'),
'46188-701338255'=>array('ausbsem'=>'6','stsem'=>'SS2007'),
'46226-049686055'=>array('ausbsem'=>'8','stsem'=>'SS2007'),
'36230-032011733'=>array('ausbsem'=>'3','stsem'=>'WS2007'),
'36337-958860002'=>array('ausbsem'=>'5','stsem'=>'WS2007'),
'36853-145301771'=>array('ausbsem'=>'7','stsem'=>'WS2007'),
'47811-899467068'=>array('ausbsem'=>'8','stsem'=>'SS2008'),
'47898-031960565'=>array('ausbsem'=>'6','stsem'=>'SS2008'),
'48010-799688191'=>array('ausbsem'=>'4','stsem'=>'SS2008'),
'40327-066184553'=>array('ausbsem'=>'5','stsem'=>'WS2008'),
'39449-346327372'=>array('ausbsem'=>'6','stsem'=>'SS2009'),
'54434-884239197'=>array('ausbsem'=>'8','stsem'=>'SS2009'),
'54772-501927272'=>array('ausbsem'=>'7','stsem'=>'WS2008'),
'43465-828491481'=>array('ausbsem'=>'7','stsem'=>'WS2009'),
'38014-570596466'=>array('ausbsem'=>'8','stsem'=>'SS2010'),

//Fehlende Semester Manuell hinzugefuegt
'47085-834502056'=>array('ausbsem'=>'0','stsem'=>'SS2002'), // Keine LVS mit Titel vorhanden
'45965-001237147'=>array('ausbsem'=>'0','stsem'=>'SS2002'), // Keine LVs mit Titel vorhanden
'45848-655736959'=>array('ausbsem'=>'0','stsem'=>'SS2002'), // Keine LVs mit Titel vorhanden
'40904-572653373'=>array('ausbsem'=>'0','stsem'=>'SS2002'), // Keine LVs mit Titel vorhanden
'54735-507042983'=>array('ausbsem'=>'1','stsem'=>'SS2010'),
'45728-490876759'=>array('ausbsem'=>'0','stsem'=>'SS2002'), // Keine LVs mit Titel vorhanden
'40924-048082305'=>array('ausbsem'=>'0','stsem'=>'SS2002'), // Keine LVs mit Titel vorhanden
'40914-251451992'=>array('ausbsem'=>'0','stsem'=>'SS2002'), // Keine LVs mit Titel vorhanden
''=>array('ausbsem'=>'0','stsem'=>'SS2002')
);

$grade_array = array(
'1'=>'1',
'2'=>'2',
'3'=>'3',
'4'=>'4',
'5'=>'5',
'ACC'=>'6', // Angerechnet
'acc'=>'6',
'inc'=>'7', // Incomplete / nicht beurteilt
'INC'=>'7',
'inc.'=>'7',
'înc'=>'7',
'p'=>'8', 	// Participated/ Teilgenommen
''=>'9'); // Noch nicht eingetragen
/*
comissionExam_boolean							-
comissionExamDate								tbl_pruefung.datum		pruefungstyp_kurzbz=kommPruef
comissionExamGrade								tbl_pruefung.note
comissionExamNotes								tbl_pruefung.anmerkung
comissionExamTeam								tbl_pruefung.anmerkung
courseDisplay_c									-
creditPointsForCalculationCurrentSemester_c		-
enrolledHours_c									-
gCheckboxGrayOutOtherSemesters_boolean			-
GPA_currentSemester_s							-
GPA_currentSemester_s_forGradeSheet				-
grade											tbl_zeugnisnote.note
gradeForCalculation_c							-
gradeForCalculationCurrentSemester_c			-
gradeWarning									-
gradeWarning_Boolean							-
noRetake_boolean_c								-
numberOfHoursForCalculationCurrentSemester_c	-
retake_boolean									-
retakeDate										tbl_pruefung.datum
retakeForCalculationCurrentSemester_c			-
retakeGrade										tbl_pruefung.note		pruefungstyp_kurzbz=termin2
retakeNotes										tbl_pruefung.anmerkung
retakeTeam										tbl_pruefung.anmerkung
semesterDisplay_c								-
z___DEVELOPER___								-
zc_recordsLabel_t								-
zc_SORT_semester_t								-
zc_SORT_studentName_t							-
zc_yearSeason_c									-
zk_A_constant_n									-
zk_F_courseID_t									Key zur Lehrveranstaltung
zk_F_studentID_t								Key zum Studierenden
zk_P_timesheetLineID_t							-
zkc_currentSemesterID_t							-
*/

$statistik_fehler=0;
$statistik_zeugnisnote_neu=0;
$statistik_zeugnisnote_fehler=0;
$statistik_pruefung_neu=0;
$statistik_pruefung_fehler=0;
$statistik_kommpruefung_fehler=0;
$statistik_kommpruefung_neu=0;
$statistik_zeugnisnote_korrektur=0;
$stg='iml';

//Alle Einträge in der Tabelle lbs_iba_course durchlaufen
$qry_noten = "SELECT * FROM sync.lbs_iml_noten";

if($result_noten = $db->db_query($qry_noten))
{
	while($row_noten = $db->db_fetch_object($result_noten))
	{
		$uid='';
		$studiensemester_kurzbz='';
		$lehrveranstaltung_id='';

		// passende LV Suchen
		$qry_kurs = "SELECT lehrveranstaltung_id FROM sync.lbs_sync_course_lv WHERE zk_p_courseid_t=".$db->db_add_param($row_noten->zk_f_courseid_t);

		if($result_kurs = $db->db_query($qry_kurs))
		{
			if($row_kurs = $db->db_fetch_object($result_kurs))
			{
				$lehrveranstaltung_id = $row_kurs->lehrveranstaltung_id;
			}
			else
			{
				logMessage("Es wurde keine Lehrveranstaltung zur ID $row_noten->zk_f_courseid_t gefunden");
				$statistik_fehler++;
			}
		}

		// passenden Studierenden Suchen
		$qry_student = "SELECT uid FROM sync.lbs_sync_student_uid WHERE zk_p_studentid_t=".$db->db_add_param($row_noten->zk_f_studentid_t);

		if($result_student = $db->db_query($qry_student))
		{
			if($row_student = $db->db_fetch_object($result_student))
			{
				$uid = $row_student->uid;
			}
			else
			{
				logMessage("Es wurde kein Studierender zur ID $row_noten->zk_f_studentid_t gefunden");
				$statistik_fehler++;
			}
		}

		// Semester / Jahr der LV suchen
		// Das Semester/Jahr wird aus der Synctablle fuer die Kurse ermittelt da fuer
		// jedes Semester eine eigene LV angelegt wird
		$qry_kurs = "SELECT zk_f_semesterid_t FROM sync.lbs_".$stg."_course WHERE zk_p_courseid_t=".$db->db_add_param($row_noten->zk_f_courseid_t);

		if($result_kurs = $db->db_query($qry_kurs))
		{
			if($row_kurs = $db->db_fetch_object($result_kurs))
			{
				$studiensemester_kurzbz = $semester[$row_kurs->zk_f_semesterid_t]['stsem'];
				$ausbildungssemester= $semester[$row_kurs->zk_f_semesterid_t]['ausbsem'];
			}
			else
			{
				logMessage("Semester der Lehrveranstaltung $row_noten->zk_f_courseid_t konnte nicht ermittelt werden");
				$statistik_fehler++;
			}
		}
		
		if($uid!='' && $lehrveranstaltung_id!='' && $studiensemester_kurzbz!='')
		{
			$zeugnisnote = new zeugnisnote();
			if(!$zeugnisnote->load($lehrveranstaltung_id, $uid, $studiensemester_kurzbz))
			{
				
				$zeugnisnote->note = $grade_array[$row_noten->grade];
				$zeugnisnote->studiensemester_kurzbz = $studiensemester_kurzbz;
				$zeugnisnote->lehrveranstaltung_id = $lehrveranstaltung_id;
				$zeugnisnote->student_uid = $uid;
				$zeugnisnote->new=true;
				if($zeugnisnote->save())
				{
					$statistik_zeugnisnote_neu++;
				}
				else
				{
					logMessage("Fehler beim Anlegen der Note: ".$zeugnisnote->errormsg);
					$statistik_zeugnisnote_fehler++;
				}
			}
			else
			{
				//wenn sich die Note unterscheidet -> updaten
				if($zeugnisnote->note!=$grade_array[$row_noten->grade])
				{
					$zeugnisnote->note = $grade_array[$row_noten->grade];
					$zeugnisnote->new=false;
					if($zeugnisnote->save())
					{
						$statistik_zeugnisnote_korrektur++;
					}
					else
					{
						logMessage("Fehler beim korrigieren der Note von $uid (".$row_noten->zk_f_studentid_t."): $lehrveranstaltung_id : ".$zeugnisnote->errormsg);
						$statistik_zeugnisnote_fehler++;
					}
				}

			}
	

			if($row_noten->retake_boolean=='1')
			{
				// Pruefung 2. Termin

				$pruefung = new pruefung();
				$pruefung->getPruefungen($uid, 'Termin2',$lehrveranstaltung_id);
				if(count($pruefung->result)==0)
				{
					// Pruefung neu Anlegen
					$pruefung->student_uid = $uid;
					$pruefung->note = $grade_array[$row_noten->retakegrade];
					$pruefung->anmerkung=$row_noten->retakenotes;
					$pruefung->anmerkung.= " Pruefer:".$row_noten->retaketeam;
					$pruefung->new = true;
					$pruefung->datum = getDatum($row_noten->retakedate);
					$pruefung->pruefungstyp_kurzbz='Termin2';
					$pruefung->lehreinheit_id = getPruefungLehreinheit($lehrveranstaltung_id, $studiensemester_kurzbz);
					if($pruefung->save())
					{
						$statistik_pruefung_neu++;
					}
					else
					{
						$statistik_pruefung_fehler++;
						logMessage("Fehler beim Anlegen der Pruefung: ".$pruefung->errormsg);
					}
				}
				else
				{
					//Wenn nur eine Pruefung gefunden wurde
					if(count($pruefung->result)==1)
					{
						//Pruefen ob die Note gleich ist
						if($pruefung->result[0]->note != $grade_array[$row_noten->retakegrade])
						{
							//Falls unterschiedlich muss update erfolgen TODO
							logMessage("PRUEFUNGSNOTE unterschiedlich:".$uid."(".$row_noten->zk_f_studentid_t."):".$lehrveranstaltung_id."(".$db->db_add_param($row_noten->zk_f_courseid_t).') '.$pruefung->result[0]->note.'->'. $grade_array[$row_noten->retakegrade]);
							$pruefung_obj = $pruefung->result[0];
							$pruefung_obj->note = $grade_array[$row_noten->retakegrade];
							$pruefung_obj->new=false;
							if($pruefung_obj->save())
								logMessage("Pruefungsnote korrigiert");
							else
								logMessage("Fehler bei der korrektur aufgetreten:".$pruefung_obj->errormsg);
						}
					}
					else
					{
						logMessage("PRUEFUNGSNOTE Zuordnung nicht eindeutig:".$uid.':'.$lehrveranstaltung_id);
					}
				}
			}

			if($row_noten->comissionexam_boolean=='1')
			{
				// Kommissionelle Pruefung

				$pruefung = new pruefung();
				$pruefung->getPruefungen($uid, 'kommPruef',$lehrveranstaltung_id);
				if(count($pruefung->result)==0)
				{
					// Pruefung neu Anlegen
					$pruefung->student_uid = $uid;
					$pruefung->note = $grade_array[$row_noten->comissionexamgrade];
					$pruefung->anmerkung=$row_noten->comissionexamnotes;
					$pruefung->anmerkung.= " Pruefer:".$row_noten->comissionexamteam;
					$pruefung->new = true;
					$pruefung->pruefungstyp_kurzbz='kommPruef';
					$pruefung->datum = getDatum($row_noten->comissionexamdate);
					$pruefung->lehreinheit_id = getPruefungLehreinheit($lehrveranstaltung_id, $studiensemester_kurzbz);
					if($pruefung->save())
					{
						$statistik_kommpruefung_neu++;
					}
					else
					{
						$statistik_kommpruefung_fehler++;
						logMessage("Fehler beim Anlegen der Pruefung: ".$pruefung->errormsg);
					}
				}
				else
				{
					if(count($pruefung->result)==1)
					{
						if($pruefung->result[0]->note!=$grade_array[$row_noten->comissionexamgrade])
						{
							logMessage("KOMMPruefungsnote unterschiedlich $uid $lehrveranstaltung_id");
						}
					}
					else
					{
						logMessage("KOMMPruefungszuordnung nicht eindeutig $uid $lehrveranstaltung");
					}

				}
			}
		}
	}
}


logMessage("----------------------------------------------------");
logMessage("Allgemeine Fehler: $statistik_fehler");
logMessage("Korrektur: $statistik_zeugnisnote_korrektur");
logMessage("Zeugnisnote: Neu $statistik_zeugnisnote_neu / Fehler $statistik_zeugnisnote_fehler");
logMessage("Pruefung: Neu $statistik_pruefung_neu / Fehler $statistik_pruefung_fehler");
logMessage("Komm. Pruefung: Neu $statistik_kommpruefung_neu / Fehler $statistik_kommpruefung_fehler");

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

/** 
 * Liefert eine Lehreinheit fuer eine Pruefung
 * Wenn zu der Lehrveranstaltung keine Lehreinheit vorhanden ist, wird eine angelegt
 */
function getPruefungLehreinheit($lehrveranstaltung_id, $studiensemester_kurzbz)
{
	global $db, $statistik_fehler;

	$qry = "SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
			WHERE lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id)." 
			AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)."
			ORDER BY lehreinheit_id LIMIT 1";
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			return $row->lehreinheit_id;
		}
	}

	// Wenn keine passende Lehreinheit gefunden wurde, muss eine neue (leere) Lehreinheit angelegt werden

	$lehreinheit= new lehreinheit();
	$lehreinheit->lehrveranstaltung_id = $lehrveranstaltung_id;
	$lehreinheit->studiensemester_kurzbz = $studiensemester_kurzbz;
	$lehreinheit->raumtyp='Dummy';
	$lehreinheit->raumtypalternativ='Dummy';
	$lehreinheit->wochenrythmus='0';
	$lehreinheit->startkw='0';
	$lehreinheit->stundenblockung='0';
	$lehreinheit->sprache='English';
	$lehreinheit->lehre=false;
	$lehreinheit->lehrfach_id=getPruefungLehrfach($lehrveranstaltung_id);

	$lehreinheit->new=true;
	if($lehreinheit->save())
	{
		return $lehreinheit->lehreinheit_id;
	}
	else
	{
		logMessage("Fehler beim Anlegen der Lehreinheit fuer eine Pruefung:".$lehreinheit->errormsg);
		$statistik_fehler++;
		return 0;
	}
}

/**
 * Liefert eine Lehrfach_id fuer eine Lehreinheit fuer eine Pruefung
 * Wenn kein passendes Lehrfach vorhanden ist, wird eines angelegt
 */
function getPruefungLehrfach($lehrveranstaltung_id)
{
	global $db, $statistik_fehler;

	$qry = "SELECT lehrfach_id FROM lehre.tbl_lehrfach 
			WHERE (studiengang_kz, semester, bezeichnung) IN (
					SELECT studiengang_kz, semester, bezeichnung 
					FROM lehre.tbl_lehrveranstaltung 
					WHERE lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id).");";

	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			return $row->lehrfach_id;
		}
	}

	$lv = new lehrveranstaltung();
	$lv->load($lehrveranstaltung_id);

	// Kein Lehrfach vorhanden -> Anlegen
	$lehrfach = new lehrfach();
	$lehrfach->new = true;
	$lehrfach->fachbereich_kurzbz='Dummy';
	$lehrfach->kurzbz=$lv->kurzbz;
	$lehrfach->bezeichnung = $lv->bezeichnung;
	$lehrfach->semester =$lv->semester;
	$lehrfach->studiengang_kz=$lv->studiengang_kz;
	$lehrfach->sprache='English';
	$lehrfach->aktiv=true;
	if($lehrfach->save())
	{
		return $lehrfach->lehrfach_id;
	}
	else
	{
		logMessage("Fehler beim Anlegen eines Lehrfachs fuer eine Pruefung:".$lehrfach->errormsg);
		$statistik_fehler++;
		return 0;
	}
}

/**
 * Wandelt Datum vom Format mm/dd/yyyy nach yyyy-mm-dd um
 */
function getDatum($date)
{
	global $statistik_fehler;

	if($date=='')
		return '';

	if(mb_ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})",$date, $regs))
	{
		return date('Y-m-d', mktime(0,0,0,$regs[1],$regs[2],$regs[3]));
	}
	else
	{
		logMessage("Ungueltiges Datumsformat fuer Pruefung:".$date);
		$statistik_fehler++;
		return '';
	}
}
?>
