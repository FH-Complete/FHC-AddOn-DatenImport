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
require_once('../../../include/person.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/adresse.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/bankverbindung.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/bisverwendung.class.php');
require_once('../../../include/bisfunktion.class.php');
require_once('../../../include/entwicklungsteam.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
	<title>LBS Datenmigration - CSV Import</title>
</head>
<body>
<h1>LBS Datenmigration - Faculty</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$datum = new datum();

if(!$rechte->isBerechtigt('addon/lbsdatenmigration'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$db = new basis_db();
$logfile='faculty_sync.log';

// Logfile oeffnen
if(!$loghandle=fopen($logfile, 'w'))
	die("Kann Logfile $logfile nicht öffnen!");

logMessage("Starte Datenuebernahme");

$data_array_nationen=array(
	"Austrian"=>"A",
	"Austria"=>"A",
	"Wien"=>"A",
	"Österreich"=>"A",
	"UK"=>"GB",
	"Poland"=>"PL",
	"Schweitz"=>"CH",
	"CH"=>"CH",
	"BRD"=>"D",
	"Italien"=>"I",
	"NÖ"=>"A",
	""=>null);

$data_array_geschlecht=array(
	"female"=>"w",
	"male"=>"m",
	"" => "u");
/**
Status der Spaltenuebernahme

zk_P_lecturerID_t 		Primary Key des Datensatzes
workAddressZip			tbl_adresse.plz        typ=f
workAddressState 		tbl_adresse.nation     typ=f
workAddressLine1		tbl_adresse.strasse	   typ=f
workAddressCompany		tbl_adresse.firma_id?  typ=f
workAddressCity			tbl_adresse.ort        typ=f
title 					tbl_person.titelpre
svNumber				tbl_person.svnr
status					tbl_benutzer.aktiv
specialSubjects			tbl_mitarbeiter.anmerkung
specials_zBvegetarian	- (nicht relevant)
sex						tbl_person.geschlecht
researchPerformances	tbl_mitarbeiter.anmerkung
reference				tbl_mitarbeiter.anmerkung
recommendedBy			tbl_mitarbeiter.anmerkung
privateAddressZip		tbl_adresse.plz		typ=h
privateAddressState		tbl_adresse.nation	typ=h
privateAddressLine2		- (leer)
privateAddressLine1		tbl_adresse.strasse	typ=h
privateAddressCity		tbl_adresse.ort		typ=h
phoneMobile				tbl_kontakt.kontakt	typ=mobil
phoneMain				tbl_kontakt.kontakt typ=telefon
phoneHome				tbl_kontakt.kontakt typ=so.tel
personalNumberBIS		?? ist das die aktuelle Personalnummer (enthaelt buchstaben)
nameLast				tbl_person.nachname
nameFirst				tbl_person.vorname
mainJobCode				bis.tbl_bisverwendung.hauptberufcode
MainJob_IMM				bis.tbl_bisverwendung.hauptberufcode
MainJob_IML				bis.tbl_bisverwendung.hauptberufcode
MainJob_IBA				bis.tbl_bisverwendung.hauptberufcode
lecturerNumber			tbl_mitarbeiter.personalnummer
ktoNrBlz				tbl_bankverbindung.blz
ktoNr					tbl_bankverbindung.kontonr
jobUsage				bis.tbl_bisverwendung.verwendung_code
jobType2				bis.tbl_bisverwendung.ba2code
jobType1				bis.tbl_bisverwendung.ba1code
jobDimension			bis.tbl_bisverwendung.beschausmasscode
hobbies					tbl_mitarbeiter.anmerkung
highestEducation		tbl_mitarbeiter.ausbildungcode
habilation				tbl_bisverwendung.habilitation   (0=keine, 1=habilitation, 2=gleichwertig, 3 berufliche tätigkeit, null)
function				tbl_person.anmerkung
FullTimeJob_IMM			tbl_bisverwendung.hauptberuflich (n/y/null)
FullTimeJob_IML			tbl_bisverwendung.hauptberuflich (n/y/null)
FullTimeJob_IBA			tbl_bisverwendung.hauptberuflich (n/y/null)
fullTimeJob				? (n/N/y)
feeArrangement			- (nicht relevant)
faxMain					tbl_kontakt.kontakt typ=fax
expertAt				tbl_person.anmerkung
emailSchool				tbl_benutzer.alias
emailPrivate			tbl_kontakt.kontakt typ=email
developmentTeam_IMM		tbl_entwicklungsteam
developmentTeam_IML		tbl_entwicklungsteam
developmentTeam_IBA		tbl_entwicklungsteam
developmentTeam			tbl_entwicklungsteam
Datum_IMM				- leer
Datum_IML				- leer
Datum_IBA				- leer
dateOfBirth				tbl_person.gebdatum
dateCreation			- (nicht relevant)
created_by				tbl_person.insertvon
created					-
contractWithLBS			tbl_person.anmerkung
contactVia				tbl_person.anmerkung
conditions				tbl_person.anmerkung
citizenship				tbl_person.staatsbuergerschaft
changed_by				tbl_person.updatevon
changed					-
SpW_IMM					tbl_bisfunktion.sws
SpW_IML					tbl_bisfunktion.sws
SpW_IBA					tbl_bisfunktion.sws
specificQualification_IMM 	tbl_entwicklungsteam.besqualcode
specificQualification_IML 	tbl_entwicklungsteam.besqualcode
specificQualification_IBA 	tbl_entwicklungsteam.besqualcode
specificQualification		tbl_entwicklungsteam.besqualcode
*/

$statistik_neue_personen=0;
$statistik_gefundene_personen=0;
$statistik_warnung=0;
$statistik_adresse_neu=0;
$statistik_adresse_fehler=0;
$statistik_kontakt_neu=0;
$statistik_kontakt_fehler=0;
$statistik_kontakt_aktualisiert=0;
$statistik_bankverbindung_neu=0;
$statistik_bankverbindung_fehler=0;
$statistik_mitarbeiter_neu=0;
$statistik_mitarbeiter_fehler=0;
$statistik_verwendung_neu=0;
$statistik_verwendung_fehler=0;
$statistik_verwendung_aktualisiert=0;
$statistik_bisfunktion_neu=0;
$statistik_bisfunktion_fehler=0;
$statistik_bisfunktion_aktualisiert=0;
$statistik_entwicklungsteam_neu=0;
$statistik_entwicklungsteam_fehler=0;
$statistik_entwicklungsteam_aktualisiert=0;

//Alle Einträge in der Tabelle lbs_lecturer durchlaufen
$qry_lecturer = "SELECT * FROM sync.lbs_lecturer";
if($result_lecturer = $db->db_query($qry_lecturer))
{
	while($row_lecturer = $db->db_fetch_object($result_lecturer))
	{
		$person_id='';
		$uid='';
		$geburtsdatum='';
		$svnr='';
		$error = false;

		// ---- PERSONENDATENSATZ

		//Schauen ob die Person schon angelegt wurde (Es gibt einen Datensatz mit NULL im PK)
		if($row_lecturer->zk_p_lecturerid_t!='')
			$qry_person = "SELECT * FROM sync.lbs_sync_lecturer_person WHERE zk_p_lecturerid_t=".$db->db_add_param($row_lecturer->zk_p_lecturerid_t);
		else
			$qry_person = "SELECT * FROM sync.lbs_sync_lecturer_person WHERE zk_p_lecturerid_t is null";

		if($result_person = $db->db_query($qry_person))
		{
			if($row_person = $db->db_fetch_object($result_person))
			{
				// Personendatensatz wurde gefunden
				$person_id = $row_person->person_id;
			}
			else
			{
				// Geburtsdatum ist im format mm/dd/yyyy gespeichert
				if($row_lecturer->dateofbirth=='')
				{
					$geburtsdatum='';
					logMessage("Warnung: $row_lecturer->namefirst $row_lecturer->namelast hat kein Geburtsdatum");
				}
				elseif(mb_ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})",$row_lecturer->dateofbirth, $regs))
				{
					$geburtsdatum = date('Y-m-d', mktime(0,0,0,$regs[1],$regs[2],$regs[3]));
				}
				else
				{
					logMessage("Error beim Parsen des Geburtsdatum: ".$row_lecturer->dateofbirth);
				}

				// SVNR ist Teilweise nur 4 stellig gespeichert und muss mit geburtsdatum erweitert werden
				if(mb_strlen($row_lecturer->svnumber)==4)
					$svnr = trim($row_lecturer->svnumber).$datum->formatDatum($geburtsdatum,'dmy');
				else
					$svnr = trim($row_lecturer->svnumber);

				// Muell aus der SVNR filtern
				$svnr = str_replace(" ","",$svnr);
				$svnr = str_replace(";","",$svnr);

				// Wenn die SVNR nicht 10 Zeichen lang ist wird diese geleert da sie falsch ist
				if(mb_strlen($svnr)!=10)
				{
					$svnr='';
					logMessage("Warnung SVNR von $row_lecturer->namelast $row_lecturer->namefirst ($row_lecturer->zk_p_lecturerid_t) ist ungueltig");
					$statistik_warnung++;
				}
				else
				{
					// Prüfen ob bereits eine Person mit dieser SVNR vorhanden ist

					$qry_svnr = "SELECT person_id FROM public.tbl_person WHERE svnr=".$db->db_add_param($svnr);
					if($result_svnr = $db->db_query($qry_svnr))
					{
						if($row_svnr = $db->db_fetch_object($result_svnr))
						{
							// Person mit der gleichen SVNR gefunden
							$person_id = $row_svnr->person_id;
						}
					}
				}
			}

			if($person_id=='')
			{
				// noch kein Personendatensatz vorhanden
				$person = new person();
				$person->vorname = trim($row_lecturer->namefirst);
				$person->nachname = trim($row_lecturer->namelast);
				$person->gebdatum = $geburtsdatum;
				$person->titelpre = trim($row_lecturer->title);
				$person->svnr = $svnr;
				$person->staatsbuergerschaft = $data_array_nationen[$row_lecturer->citizenship];
				$person->new = true;
				$person->aktiv = true;
				$person->geschlecht = $data_array_geschlecht[$row_lecturer->sex];
				$person->insertvon= $row_lecturer->created_by;
				$person->updatevon = $row_lecturer->changed_by;
			
				$person->anmerkungen = '';
				if($row_lecturer->function!='')
					$person->anmerkungen.=' Function:'.$row_lecturer->function;
				if($row_lecturer->expertat!='')
					$person->anmerkungen.=' ExpertAt:'.$row_lecturer->expertat;
				if($row_lecturer->contractwithlbs!='')
					$person->anmerkungen.=' ContractWithLBS:'.$row_lecturer->contractwithlbs;
				if($row_lecturer->contactvia!='')
					$person->anmerkungen.=' ContactVia:'.$row_lecturer->contactvia;
				if($row_lecturer->conditions!='')
					$person->anmerkungen.=' Conditions:'.$row_lecturer->conditions;

				if($person->save())
				{
					$person_id = $person->person_id;
					$qry = "INSERT INTO sync.lbs_sync_lecturer_person(zk_p_lecturerid_t,person_id) 
							VALUES(".$db->db_add_param($row_lecturer->zk_p_lecturerid_t).",".
							$db->db_add_param($person_id).");";
					if(!$db->db_query($qry))
					{
						logMessage("Fehler beim Schreiben des Sync Eintrages:".$qry);
						$error=true;
					}
				}
				else
				{
					logMessage("Fehler beim Anlegen der Person $person->vorname $person->nachname:".$person->errormsg);
					//logMessage("SVNR:".$svnr);
					$error = true;
				}


				if(!$error)
				{
					$statistik_neue_personen++;
					logMessage("Person ".$person->vorname.' '.$person->nachname.' neu angelegt');
				}
			}
			else
			{	
				// TODO Personendaten UPDATE
				$statistik_gefundene_personen++;
			}

			if($person_id!='')
			{
				// --- Adressdatensatz

				// Workaddress uebernehmen falls nicht leer
				if($row_lecturer->workaddressline1!='' || $row_lecturer->workaddresscity!='' ||
   				   $row_lecturer->workaddressstate!=''|| $row_lecturer->workaddresszip!='' ||
				   $row_lecturer->workaddresscompany!='')
				{

					$qry_workadress = "SELECT * FROM public.tbl_adresse WHERE person_id=".$db->db_add_param($person_id)." AND typ='f'";
					if($result_workadress = $db->db_query($qry_workadress))
					{
						if($row_workadresse = $db->db_fetch_object($result_workadress))
						{
							// Adressendatensatz bereits vorhanden
							// TODO Update
						}
						else
						{
							// Adressdatensatz noch nicht vorhanden -> neu anlegen
							$adresse = new adresse();
							$adresse->new=true;
							$adresse->person_id=$person_id;	
							$adresse->plz=trim($row_lecturer->workaddresszip);
							$adresse->strasse = trim($row_lecturer->workaddressline1);
							$adresse->ort = trim($row_lecturer->workaddresscity);
							$adresse->gemeinde = trim($row_lecturer->workaddresscity);
							$adresse->nation = $data_array_nationen[$row_lecturer->workaddressstate];
							$adresse->typ='f'; // Firmenadresse
							$adresse->heimatadresse=false;
							$adresse->zustelladresse=false;
							$adresse->firma_id=null; 
							// Sollen wir hier wirklich fuer jeden eine Firma anlegen
							//falls ja dann aus workadresscompany eine firma machen
							$adresse->name = $row_lecturer->workaddresscompany;

							if($adresse->save())
							{
								$statistik_adresse_neu++;
							}
							else
							{
								logMessage("Fehler beim Anlegen der Adresse:".$adresse->errormsg);
								$statistik_adresse_fehler++;
							}
						}
					}
				}

				// Privateaddress uebernehmen falls nicht leer
				if($row_lecturer->privateaddressline1!='' || $row_lecturer->privateaddresscity!='' ||
   				   $row_lecturer->privateaddressstate!=''|| $row_lecturer->privateaddresszip!='')
				{

					$qry_workadress = "SELECT * FROM public.tbl_adresse WHERE person_id=".$db->db_add_param($person_id)." AND typ='h'";
					if($result_workadress = $db->db_query($qry_workadress))
					{
						if($row_workadresse = $db->db_fetch_object($result_workadress))
						{
							// Adressendatensatz bereits vorhanden
							// TODO Update
						}
						else
						{
							// Adressdatensatz noch nicht vorhanden -> neu anlegen
							$adresse = new adresse();
							$adresse->new=true;
							$adresse->person_id=$person_id;	
							$adresse->plz=trim($row_lecturer->privateaddresszip);
							$adresse->strasse = trim($row_lecturer->privateaddressline1);
							$adresse->ort = trim($row_lecturer->privateaddresscity);
							$adresse->gemeinde = trim($row_lecturer->privateaddresscity);
							$adresse->nation = $data_array_nationen[trim($row_lecturer->privateaddressstate)];
							$adresse->typ='h';
							$adresse->heimatadresse=false;
							$adresse->zustelladresse=true;
							$adresse->firma_id=null; 

							if($adresse->save())
							{
								$statistik_adresse_neu++;
							}
							else
							{
								logMessage("Fehler beim Anlegen der Adresse:".$adresse->errormsg);
								$statistik_adresse_fehler++;
							}
						}
					}
				}

				// -- Kontaktdaten
				sync_kontakt($person_id, 'mobil', $row_lecturer->phonemobile);
				sync_kontakt($person_id, 'telefon', $row_lecturer->phonemain);
				sync_kontakt($person_id, 'so.tel', $row_lecturer->phonehome);
				sync_kontakt($person_id, 'fax', $row_lecturer->faxmain);
				sync_kontakt($person_id, 'email', $row_lecturer->emailprivate);

				// -- Bankverbindung

				if($row_lecturer->ktonr!='' || $row_lecturer->ktonrblz!='')
				{
					$qry_bankverbindung = "SELECT * FROM public.tbl_bankverbindung WHERE person_id=".$db->db_add_param($person_id);
					if($result_bankverbindung=$db->db_query($qry_bankverbindung))
					{
						if($row_bankverbindung = $db->db_fetch_object($result_bankverbindung))
						{
							//TODO Update
						}
						else
						{
							// Neue Bankverbindung anlegen
							$bv = new bankverbindung();
							$bv->person_id=$person_id;
							$bv->new = true;
							$bv->kontonr=$row_lecturer->ktonr;
							$bv->blz = $row_lecturer->ktonrblz;
							$bv->verrechnung=true;
							$bv->typ='p';
							if($bv->save())
							{
								$statistik_bankverbindung_neu++;
							}
							else
							{
								$statistik_bankverbindung_fehler++;
								logMessage("Fehler beim Anlegen der Bankverbindung von $row_lecturer->namelast ($person_id): ".$bv->errormsg);
							}
						}
					}
				}
			
				// -- Benutzerdatensatz / Mitarbeiterdatensatz anlegen
				
				$qry_bn = "SELECT 
							* 
						FROM 
							public.tbl_mitarbeiter 
							JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid)
						WHERE 
							person_id=".$db->db_add_param($person_id);
		
				if($result_bn = $db->db_query($qry_bn))
				{
					if($row_bn = $db->db_fetch_object($result_bn))
					{
						$uid = $row_bn->uid;
						// TODO Update
					}
					else
					{
						$benutzer = new benutzer();
						$benutzer->person_id = $person_id;
	

						// UID generieren		
						for($nn=8,$vn=0;$nn!=0;$nn--,$vn++)
						{
							$uid = mb_substr(mb_strtolower(convertProblemChars($row_lecturer->namelast)),0,$nn);
							$uid .= mb_substr(mb_strtolower(convertProblemChars($row_lecturer->namefirst)),0,$vn);
	
							$uid = mb_str_replace(' ','',$uid);
							$uid = mb_str_replace('-','',$uid);

							if(!$benutzer->uid_exists($uid))
								if($benutzer->errormsg=='')
									break;
						}

						$benutzer->uid = $uid;

						if($row_lecturer->emailschool!='')
							$benutzer->alias=mb_substr($row_lecturer->emailschool,0,mb_strpos($row_lecturer->emailschool,'@'));
	
						if($benutzer->alias_exists($benutzer->alias))
						{
							logMessage("Alias $benutzer->alias ist bereits vergeben und wird nicht gesetzt");
							$benutzer->alias='';
							$statistik_warnung++;
						}

						switch($row_lecturer->status)
						{
							case 'active':  $benutzer->bnaktiv = true; break;
							case 'passive': $benutzer->bnaktiv = false;break;
							default: $benutzer->bnaktiv=false;
						}

						if($benutzer->save(true,false))
						{
							//Mitarbeiter anlegen
							$mitarbeiter = new mitarbeiter();
							$mitarbeiter->uid=$uid;
							$mitarbeiter->personalnummer = $row_lecturer->lecturernumber;
							$mitarbeiter->kurzbz = $uid;
							$mitarbeiter->lektor=true;
							$mitarbeiter->fixangestellt=false;
							$mitarbeiter->stundensatz='80';
							$mitarbeiter->ausbildungscode=$row_lecturer->highesteducation;
							$mitarbeiter->ort_kurzbz=null;
							$mitarbeiter->insertamum=date('Y-m-d');
							$mitarbeiter->insertvon='Datenuebernahme';
							$anmerkung='';
							if($row_lecturer->specialsubjects!='')
								$anmerkung.=' SpecialSubjects:'.$row_lecturer->specialsubjects;

							if($row_lecturer->researchperformances!='')
								$anmerkung.=' Research:'.$row_lecturer->researchperformances;

							if($row_lecturer->reference!='')
								$anmerkung.=' Reference:'.$row_lecturer->reference;

							if($row_lecturer->recommendedby!='')
								$anmerkung.=' Recommended by:'.$row_lecturer->recommendedby;

							if($row_lecturer->hobbies!='')
								$anmerkung.=' Hobbies:'.$row_lecturer->hobbies;

							$mitarbeiter->anmerkung=$anmerkung;
							$mitarbeiter->bismelden=true;
							$mitarbeiter->standort_id=null;
							if($mitarbeiter->save(true,false))
							{
								$statistik_mitarbeiter_neu++;
							}
							else
							{
								$statistik_mitarbeiter_fehler++;
								logMessage("Fehler beim Anlegen des Mitarbeiterdatensatzes fuer $person_id:".$mitarbeiter->errormsg);
								$uid='';
							}
						}
						else
						{
							$uid='';
							logMessage("Fehler beim Anlegen des Benutzers für $person_id:".$benutzer->errormsg);
						}
					}
				}
				
				if($uid!='')
				{
					$bisverwendung_id='';
					//BIS-Daten
					$qry_verwendung = "SELECT * FROM bis.tbl_bisverwendung WHERE mitarbeiter_uid=".$db->db_add_param($uid);

					if($result_verwendung = $db->db_query($qry_verwendung))
					{
						if($row_verwendung = $db->db_fetch_object($result_verwendung))
						{
							// Verwendung ist bereits vorhanden und wird aktualisiert
							$bisverwendung_id=$row_verwendung->bisverwendung_id;
							$verwendungupdate='';
							$verwendung = new bisverwendung();
							if($verwendung->load($bisverwendung_id))
							{
								//BA1Code
								if($verwendung->ba1code!=$row_lecturer->jobtype1 
								&& !(trim($row_lecturer->jobtype1)=='' && $verwendung->ba1code==4))
								{
									$verwendung->ba1code=$row_lecturer->jobtype1;
									if($verwendung->ba1code=='')
										$verwendung->ba1code=4;

									$verwendungupdate.=" ba1code von $row_verwendung->ba1code auf $verwendung->ba1code geändert";
								}

								//BA2Code
								if($verwendung->ba2code!=$row_lecturer->jobtype2 
								&& !(trim($row_lecturer->jobtype2)=='' && $verwendung->ba2code==1))
								{
									$verwendung->ba2code=$row_lecturer->jobtype2;
									if($verwendung->ba2code=='')
										$verwendung->ba2code=1;

									$verwendungupdate.=" ba2code von $row_verwendung->ba2code auf $verwendung->ba2code geändert";
								}

								//Beschäftigungsausmass
								if($verwendung->beschausmasscode!=$row_lecturer->jobdimension
								&& !($row_lecturer->jobdimension=='' && $verwendung->beschausmasscode==2))
								{
									$verwendung->beschausmasscode=$row_lecturer->jobdimension;
									if($verwendung->beschausmasscode=='')
										$verwendung->beschausmasscode=2;

									$verwendungupdate.=" beschausmasscode von $row_verwendung->beschausmasscode auf $verwendung->beschausmasscode geändert";
								}

								//Verwendungscode
								if($verwendung->verwendung_code!=$row_lecturer->jobusage
								&& !($row_lecturer->jobusage=='' && $verwendung->verwendung_code==1))
								{
									$verwendung->verwendung_code=$row_lecturer->jobusage;
									if($verwendung->verwendung_code=='')
										$verwendung->verwendung_code=1;

									$verwendungupdate.=" verwendung_code von $row_verwendung->verwendung_code auf $verwendung->verwendung_code geändert";
								}

								//Habilitation
								switch($row_lecturer->habilation)
								{
									case '0': $sollhabilitation = false; break;
									case '1': $sollhabilitation = true; break;
									case '2': $sollhabilitation = true; break;
									case '3': $sollhabilitation = false; break;
									default:  $sollhabilitation = false; break;
								}

								if($verwendung->habilitation!=$sollhabilitation)
								{
									$verwendung->habilitation=$sollhabilitation;
									$verwendungupdate.=" Habilitation von $row_verwendung->habilitation auf $verwendung->habilitation geändert";
								}

								//Hautpberuflich
								if($row_lecturer->fulltimejob_imm=='y' || $row_lecturer->fulltimejob_iml=='y' 
								|| $row_lecturer->fulltimejob_iba=='y' || $row_lecturer->fulltimejob='y')
								{
									if(!$verwendung->hauptberuflich)
									{
										$verwendung->hauptberuflich=true;
										$verwendungupdate.=" Hauptberuflich auf true gesetzt";
									}
								}
								elseif($verwendung->hautpberuflich)
								{
									$verwendung->hauptberuflich=false;
									$verwendungupdate.=" Hauptberuflich auf false gesetzt";
								}

								if($row_lecturer->mainjob_imm!='')
									$sollhauptberufcode=$row_lecturer->mainjob_imm;
								if($row_lecturer->mainjob_iml!='')
									$sollhauptberufcode=$row_lecturer->mainjob_iml;
								if($row_lecturer->mainjob_iba!='')
									$sollhauptberufcode=$row_lecturer->mainjob_iba;
								if($row_lecturer->mainjobcode!='')
									$sollhauptberufcode=$row_lecturer->mainjobcode;

								if($verwendung->hauptberufcode!=$sollhauptberufcode)
								{
									$verwendung->hauptberufcode=$sollhauptberufcode;
									$verwendungupdate.=" Hauptberufcode von $row_verwendung->hauptberufcode auf $verwendung->hauptberufcode gesetzt";
								}

								if($verwendungupdate!='')
								{
									//Update muss durchgefuehrt werden
									$verwendung->new=false;
									if($verwendung->save())
									{
										$statistik_verwendung_aktualisiert++;
										logMessage("Verwendung von $uid wurde aktualisiert: ".$verwendungupdate);
									}
									else
									{
										$statistik_verwendung_fehler++;
										logMessage("Fehler beim Aktualisieren der Verwendung von $uid: ".$verwendung->errormsg);
									}
								}
							}
						}
						else
						{
							// Verwendung ist noch nicht vorhanden -> Neu anlegen

							$verwendung = new bisverwendung();
						
							$verwendung->new=true;
							$verwendung->ba1code=$row_lecturer->jobtype1;
							if($verwendung->ba1code=='')
								$verwendung->ba1code=4; // Freier Dienstvertrag

							$verwendung->ba2code=$row_lecturer->jobtype2;
							if($verwendung->ba2code=='')
								$verwendung->ba2code=1; // Befristet

							$verwendung->beschausmasscode=$row_lecturer->jobdimension;
							if($verwendung->beschausmasscode=='')
								$verwendung->beschausmasscode=2; //0-15

							$verwendung->verwendung_code=$row_lecturer->jobusage;
							if($verwendung->verwendung_code=='')
								$verwendung->verwendung_code=1; // Lehr/Forschungs personal

							$verwendung->mitarbeiter_uid=$uid;


							// (0=keine, 1=habilitation, 2=gleichwertig, 3 berufliche tätigkeit, null)
							switch($row_lecturer->habilation)
							{
								case '0': $verwendung->habilitation = false; break;
								case '1': $verwendung->habilitation = true; break;
								case '2': $verwendung->habilitation = true; break;
								case '3': $verwendung->habilitation = false; break;
								default:  $verwendung->habilitation = false; break;
							}

							if($row_lecturer->fulltimejob_imm=='y' || $row_lecturer->fulltimejob_iml=='y' 
							|| $row_lecturer->fulltimejob_iba=='y' || $row_lecturer->fulltimejob='y')
								$verwendung->hauptberuflich=true;
						
							/* Hauptberufcode ist 4x gespeichert
							   teils mit unterschiedlichen werten
							   Bei uns wird dies nur 1x gespeichert. 
							   Es wird der aktuellste vorhandene Eintrag genommen
							*/
						
							if($row_lecturer->mainjob_imm!='')
								$verwendung->hauptberufcode=$row_lecturer->mainjob_imm;
							if($row_lecturer->mainjob_iml!='')
								$verwendung->hauptberufcode=$row_lecturer->mainjob_iml;
							if($row_lecturer->mainjob_iba!='')
								$verwendung->hauptberufcode=$row_lecturer->mainjob_iba;
							if($row_lecturer->mainjobcode!='')
								$verwendung->hauptberufcode=$row_lecturer->mainjobcode;

							if($verwendung->save())
							{
								$statistik_verwendung_neu++;
								$bisverwendung_id = $verwendung->bisverwendung_id;
							}
							else
							{
								logMessage('Fehler beim Anlegen der Verwendung für '.$uid.':'.$verwendung->errormsg);
								$statistik_verwendung_fehler++;
							}
						}
					}

					if($bisverwendung_id!='')
					{
						//bisfunktion
						sync_bisfunktion($row_lecturer->spw_imm, '90', $bisverwendung_id);
						sync_bisfunktion($row_lecturer->spw_iml, '-573', $bisverwendung_id);
						sync_bisfunktion($row_lecturer->spw_iba, '-570', $bisverwendung_id);
					}

					sync_entwicklungsteam($row_lecturer->developmentteam_imm,$row_lecturer->specificqualification_imm, $uid,'90');
					sync_entwicklungsteam($row_lecturer->developmentteam_iml,$row_lecturer->specificqualification_iml, $uid,'-573');
					sync_entwicklungsteam($row_lecturer->developmentteam_iba,$row_lecturer->specificqualification_iba, $uid,'-570');
				}
			} // if(person!='')
		} // syncresult
	} // lecturer while
}

logMessage("----------------------------------------------------");
logMessage("Neue Personen hinzugefügt:".$statistik_neue_personen);
logMessage("Gefundene Personen:".$statistik_gefundene_personen);
logMessage("Warnhinweise:".$statistik_warnung);
logMessage("Neue Adressen:".$statistik_adresse_neu);
logMessage("Fehler bei Adressen:".$statistik_adresse_fehler);
logMessage("Neue Bankverbindungen:".$statistik_bankverbindung_neu);
logMessage("Fehler bei Bankverbindungen:".$statistik_bankverbindung_fehler);
logMessage("Neue Mitarbeiter:".$statistik_mitarbeiter_neu);
logMessage("Fehler bei Mitarbeiter:".$statistik_mitarbeiter_fehler);

logMessage("Modulname: Neu / Aktualisiert / Fehler");
logMessage("Kontakte: $statistik_kontakt_neu / $statistik_kontakt_aktualisiert / $statistik_kontakt_fehler");
logMessage("BISVerwendung: $statistik_verwendung_neu / $statistik_verwendung_aktualisiert / $statistik_verwendung_fehler");
logMessage("BISFunktionen: $statistik_bisfunktion_neu / $statistik_bisfunktion_aktualisiert / $statistik_bisfunktion_fehler");
logMessage("Entwicklungsteam: $statistik_entwicklungsteam_neu / $statistik_entwicklungsteam_aktualisiert / $statistik_entwicklungsteam_aktualisiert");


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
 * Synchronisiert die Kontaktdaten
 * @param $person_id ID der Person im FHComplete
 * @param $kontakttyp email|fax|telefon|...
 * @param $kontakt telefonnummer, emailadresse, etc
 */
function sync_kontakt($person_id, $kontakttyp, $kontakt)
{
	global $db, $statistik_kontakt_neu, $statistik_kontakt_fehler;
	global $statistik_kontakt_aktualisiert;
	global $row_lecturer;

	if($kontakt!='')
	{
		$qry = "SELECT * FROM public.tbl_kontakt 
				WHERE kontakttyp=".$db->db_add_param($kontakttyp)." 
				AND person_id=".$db->db_add_param($person_id);

		if($result_kontakt = $db->db_query($qry))
		{
			if($row_kontakt = $db->db_fetch_object($result_kontakt))
			{
				//Kontakt ist bereits vorhanden -> aktualisieren
				if($row_kontakt->kontakt!=$kontakt)
				{
					$kontakt_obj = new kontakt();
					if($kontakt_obj->load($row_kontakt->kontakt_id))
					{
						$kontakt_obj->new=false;
						$kontakt_obj->kontakt=$kontakt;
						$kontakt_obj->updateamum = date('Y-m-d H:i:s');
						$kontakt_obj->updatevon='Sync';
						if($kontakt_obj->save())
						{
							$statistik_kontakt_aktualisiert++;
							logMessage("Aktualisiere Kontakt von $row_lecturer->namelast ($person_id) Kontakttyp $kontakttyp von $row_kontakt->kontakt auf $kontakt ");
						}
						else
						{
							$statistik_kontakt_fehler++;
							logMessage("Fehler PersonID $person_id Kontakttyp $kontakttyp: $kontakt_obj->errorsmg");
						}
					}
				}
			}
			else
			{
				//Noch nicht vorhanden -> neu Anlegen

				$kontakt_obj = new kontakt();
				$kontakt_obj->kontakttyp=$kontakttyp;
				$kontakt_obj->kontakt=$kontakt;
				$kontakt_obj->new = true;
				$kontakt_obj->person_id=$person_id;
				$kontakt_obj->zustellung=false;

				if($kontakt_obj->save())
				{
					$statistik_kontakt_neu++;
				}
				else
				{
					$statistik_kontakt_fehler++;
					logMessage("Fehler PersonID $person_id Kontakttyp $kontakttyp: $kontakt_obj->errormsg");
				}
			}
		}
	}
}

/**
 * Synchronisiert die BIS Funktionen
 * @param $sws Semesterwochenstunden
 * @param $studiengang_kz Kennzahl des Studienganges
 * @param $bisverwendung_id ID der Verwendung im FHComplete
 */
function sync_bisfunktion($sws, $studiengang_kz, $bisverwendung_id)
{
	global $db, $statistik_bisfunktion_neu, $statistik_bisfunktion_fehler;
	global $statistik_bisfunktion_aktualisiert;

	if($sws!='')
	{
		$qry_funktion = "SELECT * FROM bis.tbl_bisfunktion 
				WHERE studiengang_kz=".$db->db_add_param($studiengang_kz)." AND bisverwendung_id=".$db->db_add_param($bisverwendung_id);

		if($result_funktion=$db->db_query($qry_funktion))
		{
			if($row_funktion = $db->db_fetch_object($result_funktion))
			{
				// Funktion ist bereits vorhanden -> aktualisieren
				if($sws!=$row_funktion->sws)
				{
					$bisfunktion=new bisfunktion();
					$bisfunktion->load($bisverwendung_id, $studiengang_kz);
					$bisfunktion->new=false;
					$bisfunktion->sws=$sws;
					$bisfunktion->updateamum=date('Y-m-d H:i:s');
					$bisfunktion->updatevon='Sync';
					if($bisfunktion->save())
					{
						$statistik_bisfunktion_aktualisiert++;
					}
					else
					{
						$statistik_bisfunktion_fehler++;
						logMessage("Fehler beim Aktualisieren einer BISFunktion:".$bisfunktion->errormsg);
					}
				}							
			}
			else
			{
				// Funktion noch nicht vorhanden -> neu anlegen
				$bisfunktion=new bisfunktion();

				$bisfunktion->new = true;
				$bisfunktion->bisverwendung_id=$bisverwendung_id;
				$bisfunktion->studiengang_kz=$studiengang_kz;
				$bisfunktion->sws = $sws;
				$bisfunktion->updateamum = date('Y-m-d H:i:s');
				$bisfunktion->updatevon = 'Sync';
				$bisfunktion->insertamum = date('Y-m-d H:i:s');
				$bisfunktion->insertvon = 'Sync';
				if($bisfunktion->save())
				{
					$statistik_bisfunktion_neu++;
				}
				else
				{
					logMessage("Fehler beim Anlegen einer BISFunktion".$bisfunktion->errormsg);
					$statistik_bisfunktion_fehler++;
				}
			}
		}
	}
}

/**
 * Synchronisiert die Entwicklungsteam-Eintraege
 * @param $devteam y|n gibt an ob er im entwicklungsteam war
 * @param $besqualcode Code der besonderen Qualifikation lt BIS
 * @param $uid
 * @oaram $studiengang_kz
 */
function sync_entwicklungsteam($devteam, $besqualcode, $uid, $studiengang_kz)
{
	global $db, $statistik_entwicklungsteam_neu, $statistik_entwicklungsteam_fehler;
	global $statistik_entwicklungsteam_aktualisiert;

	if($devteam=='y')
	{
		$qry = "SELECT * FROM bis.tbl_entwicklungsteam 
			WHERE mitarbeiter_uid=".$db->db_add_param($uid)."
			AND studiengang_kz=".$db->db_add_param($studiengang_kz);

		if($result = $db->db_query($qry))
		{
			if($row = $db->db_fetch_object($result))
			{
				$entwicklungsteam = new entwicklungsteam();
				if($entwicklungsteam->load($uid, $studiengang_kz))
				{
					if($besqualcode!=$row->besqualcode)
					{
						$entwicklungsteam->besqualcode=$besqualcode;
						$entwicklungsteam->updatevon = 'Sync';
						$entwicklungsteam->updateamum = date('Y-m-d H:i:s');
						$entwicklungsteam->new=false;
						if($entwicklungsteam->save())
						{
							$statistik_entwicklungsteam_aktualisiert++;
							logMessage("Entwicklungsteam von $uid aktualisiert");
						}
						else
						{
							$statistik_entwicklungsteam_fehler++;
							logMessage("Fehler beim Aktualisieren des Entwicklungsteams von $uid:".$entwicklungsteam->errormsg);
						}
					}
				}
			}
			else
			{
				$entwicklungsteam = new entwicklungsteam();
				$entwicklungsteam->new=true;
				$entwicklungsteam->mitarbeiter_uid = $uid;
				$entwicklungsteam->studiengang_kz=$studiengang_kz;
				$entwicklungsteam->besqualcode = $besqualcode;
				$entwicklungsteam->insertamum = date('Y-m-d H:i:s');
				$entwicklungsteam->insertvon = 'Sync';
				$entwicklungsteam->updateamum = date('Y-m-d H:i:s');
				$entwicklungsteam->updatevon = 'Sync';
				if($entwicklungsteam->save())
				{
					$statistik_entwicklungsteam_neu++;
				}
				else
				{
					$statistik_entwicklungsteam_fehler++;
					logMessage("Fehler beim Anlegen des Entwicklungsteams von $uid:".$entwicklungsteam->errormsg);
				}
			}
		}
	}
}
?>
