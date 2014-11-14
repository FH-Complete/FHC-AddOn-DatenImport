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
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/lvinfo.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
	<title>LBS Datenmigration</title>
</head>
<body>
<h1>LBS Datenmigration</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$datum = new datum();

if(!$rechte->isBerechtigt('addon/lbsdatenmigration'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$db = new basis_db();
$logfile='course_sync.log';

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

sync_course('iba');
sync_course('iml');
sync_course('imm');

fclose($loghandle);

function sync_course($studiengang_kurzbz)
{
	global $db, $datum, $semester;

	$statistik_lehrveranstaltung_neu=0;
	$statistik_lehrveranstaltung_fehler=0;
	$statistik_lehrveranstaltung_update=0;
	$statistik_lehrfach_neu=0;
	$statistik_lehrfach_fehler=0;
	$statistik_lehreinheit_neu=0;
	$statistik_lehreinheit_fehler=0;
	$statistik_lehreinheitmitarbeiter_neu=0;
	$statistik_lehreinheitmitarbeiter_fehler=0;
	$statistik_lvinfo_neu=0;
	$statistik_lvinfo_fehler=0;
	$statistik_lehreinheitmitarbeiter_update=0;
	$statistik_lehreinheitmitarbeiter_update_error=0;
	$statistik_lvinfo_update=0;
	$statistik_lvinfo_update_error=0;

	if($studiengang_kurzbz == 'iba')
		$studiengang_kz=-570;
	if($studiengang_kurzbz == 'iml')
		$studiengang_kz=-573;
	if($studiengang_kurzbz == 'imm')
		$studiengang_kz=90;	
		
	//Alle Einträge in der Tabelle lbs_iba_course durchlaufen
	$qry_course = "SELECT * FROM sync.lbs_".$studiengang_kurzbz."_course";

	if($result_course = $db->db_query($qry_course))
	{
		while($row_course = $db->db_fetch_object($result_course))
		{
			$lehrveranstaltung_id='';
			$lehrfach_id='';
			$lehreinheit_id='';
			$mitarbeiter_uid='';

			$qry_sync = "SELECT * FROM sync.lbs_sync_course_lv WHERE zk_p_courseid_t=".$db->db_add_param($row_course->zk_p_courseid_t);

			if($result_sync = $db->db_query($qry_sync))
			{
				if($row_sync = $db->db_fetch_object($result_sync))
				{
					// Lehrveranstaltung wurde gefunden
					$lehrveranstaltung_id = $row_sync->lehrveranstaltung_id;
					logMessage("LV Gefunden $row_sync->lehrveranstaltung_id");
				}
				else
				{
					//Schauen ob es bereits eine Lehrveranstaltung mit gleichen Namen und ECTS gibt
					$qry_lv = "SELECT lehrveranstaltung_id FROM lehre.tbl_lehrveranstaltung 
							WHERE bezeichnung=".$db->db_add_param($row_course->coursetitle)."
							AND kurzbz=".$db->db_add_param($row_course->coursenumber)."
							AND studiengang_kz=".$db->db_add_param($studiengang_kz)."
							AND semester=".$db->db_add_param($semester[$row_course->zk_f_semesterid_t]['ausbsem'])."
							AND ects=".$db->db_add_param($row_course->creditpoints)."
							AND semesterstunden=".$db->db_add_param($row_course->courseweekhours*15);

					if($result_lv = $db->db_query($qry_lv))
					{
						if($row_lv = $db->db_fetch_object($result_lv))
						{
							$lehrveranstaltung_id=$row_lv->lehrveranstaltung_id;

							$qry = "INSERT INTO sync.lbs_sync_course_lv (zk_p_courseid_t,lehrveranstaltung_id) 
								VALUES(".$db->db_add_param($row_course->zk_p_courseid_t).','.
								$db->db_add_param($lehrveranstaltung_id).');';
							if(!$db->db_query($qry))
							{
								$statistik_lehrveranstaltung_fehler++;
								logMessage("Fehler beim Eintragen in die Sync Tabelle");
							}

						}
					}
				}

				if($lehrveranstaltung_id=='')
				{
					logMessage("Neue Lehrveranstaltung wird angelegt");
					// Lehrveranstaltung wurde nicht gefunden und wird neu angelegt
					$lehrveranstaltung = new lehrveranstaltung();

					$lehrveranstaltung->new=true;
					$lehrveranstaltung->kurzbz = $row_course->coursenumber;
					$lehrveranstaltung->bezeichnung = $row_course->coursetitle;
					$lehrveranstaltung->studiengang_kz=$studiengang_kz;
					$lehrveranstaltung->semester = $semester[$row_course->zk_f_semesterid_t]['ausbsem'];
					$lehrveranstaltung->sprache='English';
					$lehrveranstaltung->ects=$row_course->creditpoints;
					$lehrveranstaltung->semesterstunden = $row_course->courseweekhours*15;
					$lehrveranstaltung->aktiv = true;
					$lehrveranstaltung->zeugnis=true;
					$lehrveranstaltung->lehre=true;
					$lehrveranstaltung->lehreverzeichnis=mb_strtolower(cleanCoursenumber($row_course->coursenumber));
					$lehrveranstaltung->projektarbeit=false;
					if(isset($row_course->lv_typ))
						$lehrveranstaltung->lehrform_kurzbz=$row_course->lv_typ;
					else
						$lehrveranstaltung->lehrform_kurzbz='SO';
					$lehrveranstaltung->bezeichnung_english=$row_course->coursetitle;

					if($lehrveranstaltung->save())
					{
						$lehrveranstaltung_id=$lehrveranstaltung->lehrveranstaltung_id;
						$statistik_lehrveranstaltung_neu++;

						$qry = "INSERT INTO sync.lbs_sync_course_lv (zk_p_courseid_t,lehrveranstaltung_id) 
							VALUES(".$db->db_add_param($row_course->zk_p_courseid_t).','.
							$db->db_add_param($lehrveranstaltung_id).');';
						if(!$db->db_query($qry))
						{
							$statistik_lehrveranstaltung_fehler++;
							logMessage("Fehler beim Eintragen in die Sync Tabelle");
						}
					}
					else
					{
						logMessage("Fehler beim Anlegen der LV: ".$lehrveranstaltung->errormsg);
						$statistik_lehrveranstaltung_fehler++;
					}
					
				}
				else
				{
					$lehrveranstaltung = new lehrveranstaltung();
					$lehrveranstaltung->load($lehrveranstaltung_id); 

					$lehrveranstaltung->new=false;
					$lehrveranstaltung->kurzbz = $row_course->coursenumber;
					$lehrveranstaltung->bezeichnung = $row_course->coursetitle;
					$lehrveranstaltung->studiengang_kz=$studiengang_kz;
					$lehrveranstaltung->semester = $semester[$row_course->zk_f_semesterid_t]['ausbsem'];
					$lehrveranstaltung->sprache='English';
					$lehrveranstaltung->ects=$row_course->creditpoints;
					$lehrveranstaltung->semesterstunden = $row_course->courseweekhours*15;
					$lehrveranstaltung->aktiv = true;
					$lehrveranstaltung->zeugnis=true;
					$lehrveranstaltung->lehre=true;
					$lehrveranstaltung->lehreverzeichnis=mb_strtolower(cleanCoursenumber($row_course->coursenumber));
					$lehrveranstaltung->projektarbeit=false;
					if(isset($row_course->lv_typ))
						$lehrveranstaltung->lehrform_kurzbz=$row_course->lv_typ;
					else
						$lehrveranstaltung->lehrform_kurzbz='SO';
					$lehrveranstaltung->bezeichnung_english=$row_course->coursetitle;

					if(!$lehrveranstaltung->save())
					{
						logMessage("Fehler beim Update der Lehrveranstaltung aufgetreten. id:".$lehrveranstaltung_id);
					}
					else
					{
						$statistik_lehrveranstaltung_update++;
					}
					

				}


				if($lehrveranstaltung_id!='')
				{
					//Lehrfach
					$qry_lehrfach = "SELECT lehrfach_id FROM lehre.tbl_lehrfach 
						WHERE studiengang_kz=".$db->db_add_param($studiengang_kz);

					if($row_course->coursetitle!='')
						$qry_lehrfach.="AND bezeichnung=".$db->db_add_param($row_course->coursetitle);
					else
						$qry_lehrfach.="AND bezeichnung='dummy'";

					if($row_course->coursenumber!='')
						$qry_lehrfach.="AND kurzbz=".$db->db_add_param($row_course->coursenumber)." LIMIT 1";
					else
						$qry_lehrfach.="AND kurzbz='dummy' LIMIT 1";

					if($result_lehrfach=$db->db_query($qry_lehrfach))
					{
						if($row_lehrfach = $db->db_fetch_object($result_lehrfach))
						{
							$lehrfach_id=$row_lehrfach->lehrfach_id;
						}
						else
						{
							$lehrfach=new lehrfach();
							$lehrfach->new=true;
							$lehrfach->bezeichnung=$row_course->coursetitle;
							if($lehrfach->bezeichnung=='')
								$lehrfach->bezeichnung='dummy';
							$lehrfach->kurzbz=$row_course->coursenumber;
							if($lehrfach->kurzbz=='')
								$lehrfach->kurzbz='dummy';
							$lehrfach->fachbereich_kurzbz='Dummy';
							$lehrfach->studiengang_kz=$studiengang_kz;
							$lehrfach->sprache='English';
							$lehrfach->farbe='00FF00';
							$lehrfach->aktiv=true;
							$lehrfach->semester=$semester[$row_course->zk_f_semesterid_t]['ausbsem'];

							if($lehrfach->save())
							{
								$lehrfach_id=$lehrfach->lehrfach_id;
								$statistik_lehrfach_neu++;
							}
							else
							{
								logMessage("Fehler beim Anlegen des Lehrfachs:".$lehrfach->errormsg);
								$statistik_lehrfach_fehler++;
							}
					
						}
					}
				}

				if($lehrfach_id!='')
				{
					//Lehreinheit
					$qry_le = "SELECT * FROM lehre.tbl_lehreinheit 
						WHERE lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id);
					if($result_le = $db->db_query($qry_le))
					{
						if($row_le = $db->db_fetch_object($result_le))
						{
							$lehreinheit_id=$row_le->lehreinheit_id;
						}
						else
						{
							//Lehreinheit neu anlegen
							$lehreinheit = new lehreinheit();
							$lehreinheit->new=true;
							$lehreinheit->lehrveranstaltung_id=$lehrveranstaltung_id;
							$lehreinheit->studiensemester_kurzbz=$semester[$row_course->zk_f_semesterid_t]['stsem'];						
							$lehreinheit->lehrfach_id = $lehrfach_id;
							if(isset($row_course->lv_typ))
								$lehreinheit->lehrform_kurzbz=$row_course->lv_typ;
							else
								$lehreinheit->lehrform_kurzbz='SO';
							$lehreinheit->stundenblockung=1;
							$lehreinheit->wochenrythmus=1;
							$lehreinheit->start_kw=1;
							$lehreinheit->raumtyp='Dummy';
							$lehreinheit->raumtypalternativ='Dummy';
							$lehreinheit->sprache='English';
							$lehreinheit->lehre=true;
							$lehreinheit->anmerkung='';
							
							if($row_course->finalexam_date!='')
								$lehreinheit->anmerkung.=" FinalExamDate: ".$row_course->finalexam_date;
							if($row_course->finalexam_room!='')
								$lehreinheit->anmerkung.=" FinalExamRoom: ".$row_course->finalexam_room;
							if($row_course->notes!='')
								$lehreinheit->anmerkung.=" Notes: ".$row_course->notes;

							if($lehreinheit->save())
							{
								$lehreinheit_id=$lehreinheit->lehreinheit_id;
								$statistik_lehreinheit_neu++;
							}
							else
							{
								$statistik_lehreinheit_fehler++;
								logMessage("Fehler beim Anlegen der Lehreinheit:".$lehreinheit->errormsg);
							}
						}
					}
				}
		
				if($lehreinheit_id!='')
				{	
					$qry_lem = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter 
							WHERE lehreinheit_id=".$db->db_add_param($lehreinheit_id);
					if($result_lem = $db->db_query($qry_lem))
					{
						if($row_lem = $db->db_fetch_object($result_lem))
						{
							//Lehreinheitmirarbeiter Update
							$lem = new lehreinheitmitarbeiter();
							$lem=load($lehreinheit_id);
							$lem->new=false;
							$lem->semesterstunden=$row_course->alvs;

							if($lem->save())
							{
								$statistik_lehreinheitmitarbeiter_update++;
							}
							else
							{
								$statistik_lehreinheitmitarbeiter_update_error++;
							}
						}
						else
						{
							if($row_course->zk_f_lectorid_t=='')
								$mitarbeiter_uid='_DummyLektor';
							else
							{
								//Lehreinheitmitarbeiter Eintrag noch nicht vorhanden -> Anlegen
								
								// Bei IMM sind steht in zk_p_lecturerid_t manchmal der Nachname drinnen
								$qry_mitarbeiter="SELECT mitarbeiter_uid
									FROM
										sync.lbs_sync_lecturer_person 
										JOIN public.tbl_benutzer USING(person_id)
										JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
										JOIN public.tbl_person ON(tbl_benutzer.person_id=tbl_person.person_id)
									WHERE zk_p_lecturerid_t=".$db->db_add_param($row_course->zk_f_lectorid_t)."
									OR nachname=".$db->db_add_param($row_course->zk_f_lectorid_t);

								if($result_mitarbeiter = $db->db_query($qry_mitarbeiter))
								{
									if($row_mitarbeiter=$db->db_fetch_object($result_mitarbeiter))
									{
										$mitarbeiter_uid=$row_mitarbeiter->mitarbeiter_uid;
									}
									else
									{
										$mitarbeiter_uid='';
										logMessage("Kein Lektor zu ID ".$row_course->zk_f_lectorid_t." gefunden");
									}
								}
							}
							
							if($mitarbeiter_uid!='')
							{
								$lem = new lehreinheitmitarbeiter();
								$lem->new=true;
								$lem->lehreinheit_id=$lehreinheit_id;
								$lem->mitarbeiter_uid=$mitarbeiter_uid;
								$lem->lehrfunktion_kurzbz='Lektor';
								if(isset($row_course->alvs))
									$lem->semesterstunden=$row_course->alvs;
								else
									$lem->semesterstunden=0;
								$lem->bismelden=true;
								$lem->faktor=1;
								$lem->stundensatz=80;
								if($lem->save())
								{
									$statistik_lehreinheitmitarbeiter_neu++;
								}
								else
								{
									$statistik_lehreinheitmitarbeiter_fehler++;
									logMessage("Fehler beim Speichern des Lehreinheitmitarbeiter Eintrags:".$lem->errormsg);
								}
							}
						}
					}

				}

				// LVINFO
				if($lehrveranstaltung_id!='' && $row_course->coursedescription!='')
				{
					$qry_lvinfo = "SELECT * FROM campus.tbl_lvinfo 
						WHERE lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id)." AND sprache='English'";

					if($result_lvinfo = $db->db_query($qry_lvinfo))
					{
						if($row_lvinfo = $db->db_fetch_object($result_lvinfo))
						{
							// Eintrag bereits vorhanden -> Update
							$lvinfo = new lvinfo();
							$lvinfo->load($lehrveranstaltung_id, 'English');
							$lvinfo->new=false;
							$lvinfo->lehrinhalte=$row_course->coursedescription;
							if($lvinfo->save())
							{
								$statistik_lvinfo_update++;
							}
							else
							{
								$statistik_lvinfo_update_error++;
							}
						}
						else
						{
							// Neuen Eintrag angelegen
							$lvinfo = new lvinfo();
							$lvinfo->new=true;
							$lvinfo->lehrveranstaltung_id=$lehrveranstaltung_id;
							$lvinfo->sprache='English';
							$lvinfo->lehrinhalte=$row_course->coursedescription;
							$lvinfo->aktiv=true;
							$lvinfo->genehmigt=true;
							if($lvinfo->save())
							{
								$statistik_lvinfo_neu++;
							}
							else
							{
								$statistik_lvinfo_fehler++;
								logMessage("Fehler beim Anlegen der LVInfo:".$lvinfo->errormsg);
							}
						}
					}
				}			
			}
		}

		// Alle Lehrveranstaltungen deaktivieren die in den letzten 4 Semestern 
		// keine Lehrauftäge hatten damit die Liste uebersichtlich wird
			
		$qry = "UPDATE lehre.tbl_lehrveranstaltung set aktiv=false 
				WHERE NOT EXISTS(SELECT * FROM lehre.tbl_lehreinheit 
								WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id 
								AND studiensemester_kurzbz IN ('WS2013','SS2012','WS2012','SS2011'));";
		if(!$db->db_query($qry))
		{
			logMessage("Fehler beim Deaktivieren alter LVs");
		}

	}


	logMessage("----- Studiengang ".$studiengang_kurzbz." syncronisiert-------");
	logMessage("Lehrveranstaltungen: $statistik_lehrveranstaltung_neu / $statistik_lehrveranstaltung_update / $statistik_lehrveranstaltung_fehler");
	logMessage("Lehrfach: $statistik_lehrfach_neu / 0 / $statistik_lehrfach_fehler");
	logMessage("Lehreinheit: $statistik_lehreinheit_neu / 0 / $statistik_lehreinheit_fehler");
	logMessage("Lehreinheitmitarbeiter: $statistik_lehreinheitmitarbeiter_neu / 0 / $statistik_lehreinheitmitarbeiter_fehler");
	logMessage("LVInfo: $statistik_lvinfo_neu / 0 / $statistik_lvinfo_fehler");


}

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

function cleanCoursenumber($coursenumber)
{
	$coursenumber = mb_str_replace('/','',$coursenumber);
	$coursenumber = mb_str_replace(' ','',$coursenumber);
	return $coursenumber;
}
?>


