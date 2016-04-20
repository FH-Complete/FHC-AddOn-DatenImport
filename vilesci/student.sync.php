<?php

/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 */

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/adresse.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/tw/generateuid.inc.php');
require_once('../../../include/betriebsmittel.class.php');
require_once('../../../include/betriebsmittelperson.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/lehrfach.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/datum.class.php');
require_once('../../kompetenzen/kompetenz.class.php');
require_once('../../../include/firma.class.php');
require_once('../../../include/projektarbeit.class.php');
require_once('../../../include/projektbetreuer.class.php');


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
<h1>LBS Datenmigration - CSV Import</h1>';

$data_array_geschlecht=array(
	"female"=>"w",
	"male"=>"m",
	"" => "u");

// dauer der studiengänge in semester
$data_array_stg = array(
    "ib"=>"6",
    "im"=>"4",
    "imm"=>"8"
);

$data_array_nationen=array(
    "vienna"=>"A",
    "Vienna"=>"A",
	"Austrian"=>"A",
	"Austria"=>"A",
	"Wien"=>"A",
    "A"=>"A",
	"Österreich"=>"A",
	"UK"=>"GB",
	"Poland"=>"PL",
	"Schweitz"=>"CH",
	"CH"=>"CH",
	"BRD"=>"D",
	"Italien"=>"I",
    "Israel"=>"IL",
    "Turkey"=>"TR",
    "Slovenia"=>"SLO",
    "French"=>"F",
    "USA"=>"USA",
    "American"=>"USA",
    "Serbian"=>"SB",
    "Romania"=>"R",
    "Netherlands"=>"NL",
    "Dutch"=>"NL",
    "Nigeria"=>"WAN",
    "UKR"=>"UKR",
    "Argentina"=>"RA",
    "BLR"=>"BLR",
    "Germany"=>"D",
    "Nigerian"=>"WAN",
    "Brazil"=>"BR",
    "Moldova"=>"MLD",
    "Hungary"=>"H",
    "D"=>"D",
    "Usbekistan"=>"UBK",
    "CDN"=>"CDN",
    "Uzbekistan"=>"UBK",
    "Russian"=>"RSF",
    "F"=>"F",
    "Russia"=>"RSF",
    "Mexico"=>"MEX",
    "Estland"=>"ELD",
    "Moldavia"=>"MLD",
    "CH"=>"CH",
    "Estonia"=>"ELD",
    "ELD"=>"ELD",
    "H"=>"H",
    "Czech"=>"TCH",
    "Peru"=>"PE",
    "Z"=>"Z",
    "Bosnia-Herzegovina"=>"BSH",
    "Kyrgyzstan"=>"KRG",
    "Spain"=>"E",
    "Spanish"=>"E",
    "Serbia"=>"SB",
    "Belarus"=>"BLR",
    "Zimbabwe"=>"RSR",
    "Ireland"=>"IRL",
    "South Africa"=>"ZA",
    "POLAND"=>"PL",
    "Albania"=>"AL",
    "Croatia"=>"CRO",
    "Sweden"=>"S",
    "Italy"=>"I",
    "Georgia"=>"GG",
    "India"=>"IND",
    "RSF"=>"RSF",
    "Bulgaria"=>"BG",
    "Namibia"=>"NAM",
    "British"=>"GB",
    "France"=>"F",
    "Colombia"=>"CO",
    "Columbia"=>"CO",
    "Nepal"=>"NEP",
    "IL"=>"IL",
    "Syria"=>"SYR",
    "Canada"=>"CDN",
    "Bulgary"=>"BG",
    "Bangladesh"=>"BAN",
    "Lithuania"=>"LIT",
    "Kirkistan"=>"KRG",
    "Zambia"=>"Z",
    "EAK"=>"EAK",
    "Benin"=>"DY",
    "Ukraine"=>"UKR",
    "Czech Republic"=>"TCH",
    "Tschechien"=>"TCH",
    "Latvia"=>"LLD",
    "LLD"=>"LLD",
    "Thailand"=>"T",
    "Venezuela"=>"YV",
    "Azerbaijan"=>"ASB",
    "Israeli"=>"IL",
    "Finland"=>"SF",
    "Bosnia Herzegovina"=>"BSH",
    "BG"=>"BG",
    "Tajikistan"=>"",
    "Costa Rica"=>"",
    "China"=>"CHF",
    "CHINA"=>"CHF",
    "Kenya"=>"EAK",
    "Kazakhstan"=>"KAS",
    "Kazakhstany"=>"KAS",
    "Macedonian"=>"MAZ",
    "Uruguay"=>"U",
    "Greece"=>"GR",
    "RUSSIA"=>"RSF",
    "Russland"=>"RSF",
    "Switzerland"=>"CH",
    "Israel/USA"=>"IL",
    "Austria/Israel"=>"A",
    "Israel/Austrian"=>"IL",
    "Austria/Israel/Germany"=>"A",
    "Argentina/Spain"=>"RA",
    "Romania/Austria"=>"R",
    "Germany/Ukraine"=>"D",
    "Germany/Colombia"=>"D",
    "Austria/Russia"=>"A",
    "French,Israeli"=>"F",
    "Germany/Moldova"=>"D",
    "Israel/Serbia"=>"IL",
    "USA/Israel"=>"USA",
    "Russian, Israel"=>"RSF",
    "Russia/Germany"=>"RSF",
    "Germany/Israel"=>"D",
    "Russia/Israel"=>"RSF",
    "CH/USA"=>"CH",
    "Czech/Israel"=>"TCH",
    "Israel-Hungary"=>"IL",
    "Israel/France"=>"IL",
    "Hungary/Israel"=>"H",
    "Israel/Hungary"=>"IL",
    "Netherlands/Israel"=>"NL",
    "Georgia/Israel"=>"GG",
    "GermanyIsrael"=>"D",
    "Israel/British"=>"IL",
    "Bosnia-Herzegovina/Israel"=>"BSH",
    "British/French"=>"GB",
    "Israel/Austria/Canada"=>"IL",
    "Germany/Kirkistan"=>"D",
    "Israel/Ukraine"=>"IL",
    "Russia / Israel"=>"RSF",
    "Israel/Russia"=>"IL",
    "Ukraine/Latvia"=>"UKR",
    "Austrian/Brazilian"=>"A",
    "Ukraine/Germany"=>"UKR",
    "Germany/Russia"=>"D",
    "Germany / Israel"=>"D",
    "Italy/Croatia"=>"I",
    "Israel/Czech Republic"=>"IL",
    "Austria/France"=>"A",
    "Austria/Italy"=>"A",
    "Canada/USA"=>"CDN",
    "Israel/Bosnia Herzegovina"=>"IL",
    "Israel/Romania"=>"IL",
    "Austrian  (Aserbaidschan)"=>"A",
    "Israel/Uruguay"=>"IL",
    "USA/Ukraine"=>"USA",
    "Colombia/Israel"=>"CO",
    "Australia/Austrian"=>"AUS",
    "Israel/Germany"=>"IL",
    "SLOVAKIA"=>"SQ",
    "Cyprus"=>"CY",
    "SERBIA"=>"SB",
    "ISRAEL"=>"IL",
    "GEORGIA"=>"GG",
    "CZECH  REPUBLIC"=>"TCH",
    "SPAIN"=>"E",
    "Macedonia"=>"MAZ",
    "Qatar"=>"QTR",
    "MOLDAVIA"=>"MLD",
    "MLD"=>"MLD",
    "Azerbaijan SSR"=>"ASB",
    "Moldau"=>"MLD",
    "Chisinau, Moldova"=>"MLD",
    "Republic of Moldova"=>"MLD",
    "ALBANIA"=>"AL",
    "SLOVENIA"=>"SLO",
    "Ecuador"=>"EC",
    "Ägypten"=>"ET",
    "Jerusalem"=>"IL",
    "HUNGARY"=>"H",
    "Slovakia"=>"SQ",
    "Argentina"=>"RA",
    "?France"=>"F",
    "ENGLAND"=>"GB",
    "Gibraltar"=>"",
    "UKRAINE"=>"UKR",
    "BULGARIA"=>"BG",
    "AUSTRIA"=>"A",
    "NL"=>"NL",
    "GERMANY"=>"D",
    "MACEDONIA"=>"MAZ",
    "Wyoming, USA"=>"USA",
    "Kazakhistan"=>"KAS",
    "Republic of Belarus"=>"BLR",
    "BR"=>"BR",
    "SYRIA"=>"SYR",
    "CANADA"=>"CDN",
    "NEPAL"=>"NEP",
    "NIGERIA"=>"WAN",
    "CANADA"=>"CDN",
    "BELARUS"=>"BLR",
    "KAZAKHSTAN"=>"KAS",
    "Luxembourg"=>"L",
    "Slovak Republic"=>"SQ",
    "Deutschland"=>"D",
    "Belgium"=>"B",
    "BRAZIL"=>"BR",
    "Tbilisi"=>"",
    "Toronto,Onterio-CANADA"=>"CDN",
    "Pech, Hungary"=>"H",
    "WIEN"=>"A",
    "Pakistan"=>"PAK",
    "Armenia"=>"ARM",
    "Bosnia"=>"BSH",
    "Romania/Israel"=>"R",
    "Azermarka Company"=>"",
    "Moscow"=>"RSF",
    "Israel/Bulgarian"=>"IL",
    "Jena, Deutschland"=>"D",
    "Stockholm"=>"S",
    "Hannover"=>"D",
    "Europort, Gibraltar"=>"",
	""=>null);

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$datum = new datum();

if(!$rechte->isBerechtigt('addon/lbsdatenmigration'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$db = new basis_db();
$logfile='student_sync.log';

// Logfile oeffnen
if(!$loghandle=fopen($logfile, 'w'))
	die("Kann Logfile $logfile nicht öffnen!");

logMessage("Starte Datenuebernahme");


sync_studiengang('ib');
sync_studiengang('im');
sync_studiengang('imm');

function sync_studiengang($studiengang_kz)
{
    global $db, $datum, $data_array_nationen, $data_array_geschlecht, $data_array_stg;
    $person_id = '';
    $prestudent_id = '';

    // in sync tabelle werden die langen kurzbz verwendet
    if($studiengang_kz =='ib')
        $stg_query = 'iba';

    if($studiengang_kz =='im')
        $stg_query = 'iml';

    if($studiengang_kz == 'imm')
        $stg_query = 'imm';


    $statistik_person_update = 0;
    $statistik_person_update_error =  0;
    $statistik_prestudent_update = 0;
    $statistik_prestudent_update_error = 0;
    $statistik_person_insert = 0;
    $statistik_person_insert_error =0;
    $statistik_prestudent_insert = 0;
    $statistik_prestudent_insert_error=0;
    $statistik_kontakt_insert =0;
    $statistik_kontakt_error = 0;
    $statistik_adresse_insert=0;
    $statistik_adresse_insert_error=0;
    $statistik_warnung=0;
    $statistik_benutzer_insert=0;
    $statistik_benutzer_insert_error=0;
    $statistik_student_insert=0;
    $statistik_student_insert_error=0;
    $statistik_status_insert=0;
    $statistik_status_error = 0;
    $statistik_betriebsmittel_insert = 0;
    $statistik_betriebsmittel_error = 0;
	$statistik_kompetenz_insert=0;
	$statistik_kompetenz_insert_error=0;
	$statistik_benutzer_update=0;
	$statistik_benutzer_update_error=0;
	$statistik_student_update=0;
	$statistik_student_update_error=0;
	$statistik_kontakt_aktualisiert=0;
	$statistik_firma_insert=0;
	$statistik_firma_insert_error=0;
	$statistik_adresse_insert=0;
	$statistik_adresse_insert_error=0;
	$statistik_projektarbeit_insert=0;
	$statistik_projektarbeit_insert_error=0;


    $qry_student = "SELECT * FROM sync.lbs_".$stg_query."_student where NOT EXISTS (SELECT 1 FROM sync.lbs_sync_student_uid where zk_p_studentid_t=lbs_".$stg_query."_student.zk_p_studentid_t) ;";

    if($result_student = $db->db_query($qry_student))
    {
        while($row_student = $db->db_fetch_object($result_student))
        {

            if($row_student->dateofbirth == '')
            {
                $gebdatum = '';
                logMessage("Warnung: ".$row_student->namefirst.' '.$row_student->namelast." hat kein Geburtsdatum");
            }
            elseif(mb_ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})",$row_student->dateofbirth, $regs))
            {
                $gebdatum = date('Y-m-d', mktime(0,0,0,$regs[1],$regs[2],$regs[3]));
            }
            else
            {
                logMessage("Error beim Parsen des Geburtsdatum: ".$row_student->dateofbirth);
            }
                            // SVNR ist Teilweise nur 4 stellig gespeichert und muss mit geburtsdatum erweitert werden
            if(mb_strlen($row_student->svnumber)==4)
                $svnr = trim($row_student->svnumber).$datum->formatDatum($gebdatum,'dmy');
            else
                $svnr = trim($row_student->svnumber);

            // Muell aus der SVNR filtern
            $svnr = str_replace(" ","",$svnr);
            $svnr = str_replace(";","",$svnr);

            // Wenn die SVNR nicht 10 Zeichen lang ist wird diese geleert da sie falsch ist
            if(mb_strlen($svnr)!=10)
            {

                logMessage("Warnung SVNR von $row_student->namelast $row_student->namefirst ($row_student->zk_p_studentid_t) (student_id)ist ungueltig ");
                $svnr='';
                $statistik_warnung++;
            }

            // überprüfen ob es schon einen prestudenten zum studenten gibt
            $qry_applicant = "SELECT * FROM sync.lbs_sync_applicant_person
                WHERE zk_p_applicantid_t=".$db->db_add_param($row_student->zk_f_applicantid_t).";";

            if($result_applicant = $db->db_query($qry_applicant))
            {
                if($row_applicant = $db->db_fetch_object($result_applicant))
                {
                    // Person wurde gefunden -> update auf person
                    $person_id = $row_applicant->person_id;
                    $person= new person();
                    if(!$person->load($person_id))
                        logMessage("Fehler beim laden der Person ".$person_id);
                    $person->staatsbuergerschaft = $data_array_nationen[$row_student->citizenship];
                    $person->gebdatum = $gebdatum;
                    $person->ersatzkennzeichen = trim($row_student->erskz);
                    $person->vorname = trim($row_student->namefirst);
                    $person->nachname = ucfirst(mb_strtolower($row_student->namelast));
                    $person->anmerkung.= ' '.$row_student->notespersonal;
                    $person->svnr = '';
                    $person->geschlecht = $data_array_geschlecht[$row_student->sex];
                    $person->new = false;
                    $person->person_id = $person_id;
                    if($person->save())
                    {
                        $statistik_person_update++;

                    }
                    else
                    {
                        $statistik_person_update_error++;
                        logMessage("Fehler beim Update der Person $person->vorname $person->nachname:".$person->errormsg);
                    }

                    // update auf prestudent
                    $prestudent_id=$row_applicant->prestudent_id;
                    $prestudent = new prestudent();
                    if(!$prestudent->load($prestudent_id))
                        logMessage("Fehler beim laden des Prestudenten ".$prestudent_id);
                    $prestudent->zgv_code=trim($row_student->accesscode);
                    if(is_numeric(trim($row_student->accesscodemaster)))
                        $prestudent->zgvmas_code = trim($row_student->accesscodemaster);
                    else
                        $prestudent->zgvmas_code = '';
                    $prestudent->zgvort = $data_array_nationen[$row_student->accesscountry];
                    $prestudent->zgvdatum = $datum->formatDatum($row_student->accessdate,'Y-m-d');
                    $prestudent->anmerkung .=' '.$row_student->notesacademic;
                    $prestudent->new = false;
                    if($prestudent->save())
                    {
                        $statistik_prestudent_update++;
                    }
                    else
                    {
                        $statistik_prestudent_update_error++;
                        logMessage("Fehler beim Update des Prestudenten ".$prestudent_id. ' '.$prestudent->errormsg);
                    }

					//Update auf Kompetenzen
					if($person_id!='')
					{
						$kompetenz = new kompetenz();
						$kompetenz->getKompetenzPerson($person_id);

						if($row_student->language_german != '')
						{
							$found = false;
							foreach($kompetenz->result as $k)
							{
								if($k->kompetenztyp_id == '2')
									$found = true;
							}

							if(!$found)
							{
								$kompetenz = new kompetenz();
								$kompetenz->kompetenztyp_id = '2';
								$kompetenz->person_id = $person_id;
								$kompetenz->kompetenzniveau = $row_student->language_german;
								$kompetenz->updateamum = date('Y-m-d H:i:s');
								$kompetenz->updatevon = 'Sync';
								$kompetenz->new = true;

								switch ($row_student->language_german)
								{
									case('A'):
										$kompetenz->kompetenzniveaustufe_id = '31';
										break;
									case('B'):
										$kompetenz->kompetenzniveaustufe_id = '33';
										break;
									case('C'):
										$kompetenz->kompetenzniveaustufe_id ='35';
										break;
								}

								if($kompetenz->save())
								{
									$statistik_kompetenz_insert++;
								}
								else
								{
									$statistik_kompetenz_insert_error++;
								}
							}
						}

						if($row_student->language_french != '')
						{
							$found = false;
							foreach($kompetenz->result as $k)
							{
								if($k->kompetenztyp_id == '6')
									$found = true;
							}

							if(!$found)
							{
								$kompetenz = new kompetenz();
								$kompetenz->kompetenztyp_id = '6';
								$kompetenz->person_id = $person_id;
								$kompetenz->kompetenzniveau = $row_student->language_french;
								$kompetenz->updateamum = date('Y-m-d H:i:s');
								$kompetenz->updatevon = 'Sync';
								$kompetenz->new = true;
								switch ($row_student->language_french)
								{
									case('A'):
										$kompetenz->kompetenzniveaustufe_id = '55';
										break;
									case('B'):
										$kompetenz->kompetenzniveaustufe_id = '57';
										break;
									case('C'):
										$kompetenz->kompetenzniveaustufe_id ='59';
										break;
								}

								if($kompetenz->save())
								{
									$statistik_kompetenz_insert++;
									logMessage('Kompetenz erfolgreich gespeichert');
								}
								else
								{
									$statistik_kompetenz_insert_error++;
									logMessage('Fehler bei Kompetenz speichern '.$kompetenz->errormsg);
								}
							}
						}

						if($row_student->language_hebrew != '')
						{
							$found = false;
							foreach($kompetenz->result as $k)
							{
								if($k->kompetenztyp_id == '3')
									$found = true;
							}

							if(!$found)
							{
								$kompetenz = new kompetenz();
								$kompetenz->kompetenztyp_id = '3';
								$kompetenz->person_id = $person_id;
								$kompetenz->kompetenzniveau = $row_student->language_hebrew;
								$kompetenz->updateamum = date('Y-m-d H:i:s');
								$kompetenz->updatevon = 'Sync';
								$kompetenz->new = true;

								switch ($row_student->language_hebrew)
								{
									case('A'):
										$kompetenz->kompetenzniveaustufe_id = '37';
										break;
									case('B'):
										$kompetenz->kompetenzniveaustufe_id = '39';
										break;
									case('C'):
										$kompetenz->kompetenzniveaustufe_id ='41';
										break;
								}

								if($kompetenz->save())
								{
									$statistik_kompetenz_insert++;
								}
								else
								{
									$statistik_kompetenz_insert_error++;
								}
							}
						}

						if($row_student->language_russian != '')
						{
							$found = false;
							foreach($kompetenz->result as $k)
							{
								if($k->kompetenztyp_id == '4')
									$found = true;
							}

							if(!$found)
							{
								$kompetenz = new kompetenz();
								$kompetenz->kompetenztyp_id = '4';
								$kompetenz->person_id = $person_id;
								$kompetenz->kompetenzniveau = $row_student->language_russian;
								$kompetenz->updateamum = date('Y-m-d H:i:s');
								$kompetenz->updatevon = 'Sync';
								$kompetenz->new = true;

								switch ($row_student->language_russian)
								{
									case('A'):
										$kompetenz->kompetenzniveaustufe_id = '43';
										break;
									case('B'):
										$kompetenz->kompetenzniveaustufe_id = '45';
										break;
									case('C'):
										$kompetenz->kompetenzniveaustufe_id ='47';
										break;
								}

								if($kompetenz->save())
								{
									$statistik_kompetenz_insert++;
								}
								else
								{
									$statistik_kompetenz_insert_error++;
								}

							}
						}

						if($row_student->language_spain != '')
						{
							$found = false;
							foreach($kompetenz->result as $k)
							{
								if($k->kompetenztyp_id == '5')
									$found = true;
							}

							if(!$found)
							{
								$kompetenz = new kompetenz();
								$kompetenz->kompetenztyp_id = '5';
								$kompetenz->person_id = $person_id;
								$kompetenz->kompetenzniveau = $row_student->language_spain;
								$kompetenz->updateamum = date('Y-m-d H:i:s');
								$kompetenz->updatevon = 'Sync';
								$kompetenz->new = true;

								switch ($row_student->language_spain)
								{
									case('A'):
										$kompetenz->kompetenzniveaustufe_id = '49';
										break;
									case('B'):
										$kompetenz->kompetenzniveaustufe_id = '51';
										break;
									case('C'):
										$kompetenz->kompetenzniveaustufe_id ='53';
										break;
								}

								if($kompetenz->save())
								{
									$statistik_kompetenz_insert++;
								}
								else
								{
									$statistik_kompetenz_insert_error++;
								}
							}
						}
					}
                }
            }


            if($person_id=='')
            {
                // es wurde noch keine Person gefunden -> neu anlegen
                $person= new person();
                $person->staatsbuergerschaft = $data_array_nationen[$row_student->citizenship];
                $person->gebdatum = $gebdatum;
                $person->ersatzkennzeichen = trim($row_student->erskz);
                $person->vorname = trim($row_student->namefirst);
                $person->nachname = ucfirst(mb_strtolower($row_student->namelast));
                $person->anmerkung= trim($row_student->notespersonal);
                $person->svnr = '';
                $person->geschlecht = $data_array_geschlecht[$row_student->sex];
                $person->new = true;
                $person->person_id = $person_id;
                $person->aktiv=true;
                if($person->save())
                {
                    $statistik_person_insert++;

                    $person_id = $person->person_id;
                     $qry = "INSERT INTO sync.lbs_sync_applicant_person(zk_p_applicantid_t,person_id)
                        VALUES(".$db->db_add_param($row_student->zk_f_applicantid_t).",".
                        $db->db_add_param($person_id).");";
                    if(!$db->db_query($qry))
                    {
                        logMessage("Fehler beim Schreiben des Sync Eintrages:".$qry);
                    }

                }
                else
                {
                    $statistik_person_insert_error++;
                    logMessage("Fehler beim Erstellen der Person $person->vorname $person->nachname:".$person->errormsg);
                }

                $prestudent = new prestudent();
                $prestudent->zgv_code=trim($row_student->accesscode);
                if(is_numeric(trim($row_student->accesscodemaster)))
                    $prestudent->zgvmas_code = trim($row_student->accesscodemaster);
                else
                    $prestudent->zgvmas_code = '';
                $prestudent->zgvort = $data_array_nationen[$row_student->accesscountry];
                $prestudent->zgvdatum = $datum->formatDatum(trim($row_student->accessdate),'Y-m-d');
                $prestudent->anmerkung = trim($row_student->notesacademic);
                $prestudent->person_id = $person_id;
                $prestudent->new = true;
                $prestudent->aufmerksamdurch_kurzbz = 'k.A.';
                $prestudent->person_id = $person_id;
                $prestudent->reihungstestangetreten = ($row_student->interviewtaken_boolean == '1')?true:false;
                $qry_stg = "SELECT studiengang_kz FROM public.tbl_studiengang WHERE kurzbz=".$db->db_add_param($studiengang_kz).';';
                if($result_stg = $db->db_query($qry_stg))
                {
                    if($row_stg = $db->db_fetch_object($result_stg))
                    {
                        $prestudent->studiengang_kz=$row_stg->studiengang_kz;
                    }
                }

                if($prestudent->save())
                {
                    $statistik_prestudent_insert++;

                    $prestudent_id = $prestudent->prestudent_id;
                    $qry =  "UPDATE sync.lbs_sync_applicant_person SET prestudent_id =".$db->db_add_param($prestudent->prestudent_id)."
                            WHERE zk_p_applicantid_t =".$db->db_add_param($row_student->zk_f_applicantid_t).";";
                    if(!$db->db_query($qry))
                        logMessage("Fehler beim Schreiben des Sync Eintrages:".$qry);
                }
                else
                {
                    $statistik_prestudent_insert_error++;
                    logMessage("Fehler beim Insert des Prestudenten ".$prestudent_id);
                }

				if($person_id!='')
				{
					// Kompetenzen anlegen
					// wenn vorhanden kompetenztyp anlegen
					if($row_student->language_french != '')
					{
						$kompetenz = new kompetenz();
						$kompetenz->kompetenztyp_id = '6';
						$kompetenz->person_id = $person_id;
						$kompetenz->kompetenzniveau = $row_student->language_french;
						$kompetenz->insertamum = date('Y-m-d H:i:s');
						$kompetenz->insertvon = 'Sync';
						$kompetenz->updateamum = date('Y-m-d H:i:s');
						$kompetenz->updatevon = 'Sync';
						$kompetenz->new = true;
						switch ($row_student->language_french)
						{
							case('A'):
								$kompetenz->kompetenzniveaustufe_id = '55';
								break;
							case('B'):
								$kompetenz->kompetenzniveaustufe_id = '57';
								break;
							case('C'):
								$kompetenz->kompetenzniveaustufe_id ='59';
								break;
						}

						if($kompetenz->save())
						{
							$statistik_kompetenz_insert++;
						}
						else
						{
							$statistik_kompetenz_insert_error++;
						}

					}

					if($row_student->language_german != '')
					{
						$kompetenz = new kompetenz();
						$kompetenz->kompetenztyp_id = '2';
						$kompetenz->person_id = $person_id;
						$kompetenz->kompetenzniveau = $row_student->language_german;
						$kompetenz->insertamum = date('Y-m-d H:i:s');
						$kompetenz->insertvon = 'Sync';
						$kompetenz->updateamum = date('Y-m-d H:i:s');
						$kompetenz->updatevon = 'Sync';
						$kompetenz->new = true;

						switch ($row_student->language_german)
						{
							case('A'):
								$kompetenz->kompetenzniveaustufe_id = '31';
								break;
							case('B'):
								$kompetenz->kompetenzniveaustufe_id = '33';
								break;
							case('C'):
								$kompetenz->kompetenzniveaustufe_id ='35';
								break;
						}

						if($kompetenz->save())
						{
							$statistik_kompetenz_insert++;
						}
						else
						{
							$statistik_kompetenz_insert_error++;
						}

					}

					if($row_student->language_hebrew != '')
					{
						$kompetenz = new kompetenz();
						$kompetenz->kompetenztyp_id = '3';
						$kompetenz->person_id = $person_id;
						$kompetenz->kompetenzniveau = $row_student->language_hebrew;
						$kompetenz->insertamum = date('Y-m-d H:i:s');
						$kompetenz->insertvon = 'Sync';
						$kompetenz->updateamum = date('Y-m-d H:i:s');
						$kompetenz->updatevon = 'Sync';
						$kompetenz->new = true;

						switch ($row_student->language_hebrew)
						{
							case('A'):
								$kompetenz->kompetenzniveaustufe_id = '37';
								break;
							case('B'):
								$kompetenz->kompetenzniveaustufe_id = '39';
								break;
							case('C'):
								$kompetenz->kompetenzniveaustufe_id ='41';
								break;
						}

						if($kompetenz->save())
						{
							$statistik_kompetenz_insert++;
						}
						else
						{
							$statistik_kompetenz_insert_error++;
						}

					}

					if($row_student->language_russian != '')
					{
						$kompetenz = new kompetenz();
						$kompetenz->kompetenztyp_id = '4';
						$kompetenz->person_id = $person_id;
						$kompetenz->kompetenzniveau = $row_student->language_russian;
						$kompetenz->insertamum = date('Y-m-d H:i:s');
						$kompetenz->insertvon = 'Sync';
						$kompetenz->updateamum = date('Y-m-d H:i:s');
						$kompetenz->updatevon = 'Sync';
						$kompetenz->new = true;

						switch ($row_student->language_russian)
						{
							case('A'):
								$kompetenz->kompetenzniveaustufe_id = '43';
								break;
							case('B'):
								$kompetenz->kompetenzniveaustufe_id = '45';
								break;
							case('C'):
								$kompetenz->kompetenzniveaustufe_id ='47';
								break;
						}

						if($kompetenz->save())
						{
							$statistik_kompetenz_insert++;
						}
						else
						{
							$statistik_kompetenz_insert_error++;
						}

					}

					if($row_student->language_spain != '')
					{
						$kompetenz = new kompetenz();
						$kompetenz->kompetenztyp_id = '5';
						$kompetenz->person_id = $person_id;
						$kompetenz->kompetenzniveau = $row_student->language_spain;
						$kompetenz->insertamum = date('Y-m-d H:i:s');
						$kompetenz->insertvon = 'Sync';
						$kompetenz->updateamum = date('Y-m-d H:i:s');
						$kompetenz->updatevon = 'Sync';
						$kompetenz->new = true;

						switch ($row_student->language_spain)
						{
							case('A'):
								$kompetenz->kompetenzniveaustufe_id = '49';
								break;
							case('B'):
								$kompetenz->kompetenzniveaustufe_id = '51';
								break;
							case('C'):
								$kompetenz->kompetenzniveaustufe_id ='53';
								break;
						}

						if($kompetenz->save())
						{
							$statistik_kompetenz_insert++;
						}
						else
						{
							$statistik_kompetenz_insert_error++;
						}
					}
				}
            }

            if($person_id != '')
            {
                // kontakte zusätzlich hinzufügen
                sync_kontakt($person_id, 'mobil', $row_student->phonemobile);
                sync_kontakt($person_id, 'telefon', $row_student->phonehome);
                sync_kontakt($person_id, 'email', $row_student->emailprivate);
                if(isset($row_student->emailprivateschool))
                    sync_kontakt($person_id, 'email', $row_student->emailprivateschool);
                sync_kontakt($person_id, 'emergency', $row_student->phoneemergency);

                // Adresse zusätzlich hinzufügen
                if($row_student->homeaddressline1 != '' || $row_student->homeaddressline2 != '' ||
                    $row_student->homeaddressstate != '' || $row_student->homeaddresszip != '')
                {
                    // gibt es die adresse schon
                    $qry_adresse= "SELECT * FROM public.tbl_adresse WHERE
                            person_id=".$db->db_add_param($person_id)."
                            AND plz=".$db->db_add_param($row_student->homeaddresszip).";";

                    if($result_adresse = $db->db_query($qry_adresse))
                    {
                        if($row_adresse = $db->db_fetch_object($result_adresse))
                        {
                            // schon vorhanden
                            $adresse = new adresse();
							$adresse->load($row_adresse->adresse_id);
                            $adresse->new = false;

                            $adresse->typ = 'h';
                            $adresse->heimatadresse = true;
                            $adresse->zustelladresse=true;
                            $adresse->firma_id=null;
                            $adresse->person_id = $person_id;
                            $adresse->strasse = trim($row_student->homeaddressline1).' '.trim($row_student->homeaddressline2);
                            $adresse->ort = trim($row_student->homeaddresscity);
                            $adresse->gemeinde = trim($row_student->viennaaddresscity);
                            $adresse->plz = trim($row_student->homeaddresszip);
                            $adresse->nation = $data_array_nationen[$row_student->homeaddressstate];

							if($adresse->save())
							{
								$statistik_adresse_update++;
							}
							else
							{
								$statistik_adresse_update_error++;
							}
                        }
                        else
                        {
                            // hinzufügen
                            $adresse = new adresse();
                            $adresse->new = true;

                            $adresse->typ = 'h';
                            $adresse->heimatadresse = true;
                            $adresse->zustelladresse=true;
                            $adresse->firma_id=null;
                            $adresse->person_id = $person_id;
                            $adresse->strasse = trim($row_student->homeaddressline1).' '.trim($row_student->homeaddressline2);
                            $adresse->ort = trim($row_student->homeaddresscity);
                            $adresse->gemeinde = trim($row_student->viennaaddresscity);
                            $adresse->plz = trim($row_student->homeaddresszip);
                            $adresse->nation = $data_array_nationen[$row_student->homeaddressstate];

                            if($adresse->save())
                            {
                                $statistik_adresse_insert++;
                            }
                            else
                            {
                                $statistik_adresse_insert_error++;
                                logMessage("Fehler beim Anlegen der Adresse:".$adresse->errormsg);
                            }
                        }
                    }
                }

                // Privateaddress uebernehmen falls nicht leer
                if($row_student->viennaaddresscity!='' || $row_student->viennaaddressline1!='' ||
                    $row_student->viennaaddressline2!=''|| $row_student->viennaaddresszip!='')
                {
                     // gibt es die adresse schon
                    $qry_adresse= "SELECT 1 FROM public.tbl_adresse WHERE
                            person_id=".$db->db_add_param($person_id)."
                            AND plz=".$db->db_add_param($row_student->viennaaddresszip).";";

                    if($result_adresse = $db->db_query($qry_adresse))
                    {
                        if($row_adresse = $db->db_fetch_object($result_adresse))
                        {
                            // schon vorhanden
                            $adresse = new adresse();
							$adresse->load($row_adresse->adresse_id);
                            $adresse->new=false;

                            $adresse->person_id=$person_id;
                            $adresse->plz=trim($row_student->viennaaddresszip);
                            $adresse->strasse = trim($row_student->viennaaddressline1).' '.trim($row_student->viennaaddressline2);
                            $adresse->ort = trim($row_student->viennaaddresscity);
                            $adresse->gemeinde = trim($row_student->viennaaddresscity);
                            $adresse->nation = $data_array_nationen[trim($row_student->viennaaddresscity)];
                            $adresse->typ='n';
                            $adresse->heimatadresse=false;
                            $adresse->zustelladresse=false;
                            $adresse->firma_id=null;

							if($adresse->save())
							{
								$statistik_adresse_update++;
							}
							else
							{
								$statistik_adresse_update_error++;
							}
                        }
                        else
                        {
                            // neu anlegen
                            $adresse = new adresse();
                            $adresse->new=true;

                            $adresse->person_id=$person_id;
                            $adresse->plz=trim($row_student->viennaaddresszip);
                            $adresse->strasse = trim($row_student->viennaaddressline1).' '.trim($row_student->viennaaddressline2);
                            $adresse->ort = trim($row_student->viennaaddresscity);
                            $adresse->gemeinde = trim($row_student->viennaaddresscity);
                            $adresse->nation = $data_array_nationen[trim($row_student->viennaaddresscity)];
                            $adresse->typ='n';
                            $adresse->heimatadresse=false;
                            $adresse->zustelladresse=false;
                            $adresse->firma_id=null;

                            if($adresse->save())
                            {
                                $statistik_adresse_insert++;
                            }
                            else
                            {
                                $statistik_adresse_insert_error++;
                                logMessage("Fehler beim Anlegen der Adresse:".$adresse->errormsg);
                            }
                        }
                    }
                }


                // ist der person schon eine zutrittskarte zugeordnet
                $qry_betriebsmittel="SELECT * FROM wawi.tbl_betriebsmittelperson WHERE person_id = ".$db->db_add_param($person_id, FHC_INTEGER).";";
                if($result_betriebsmittel = $db->db_query($qry_betriebsmittel))
                {
                    if($row_betriebsmittel = $db->db_fetch_object($result_betriebsmittel))
                    {
						$qry_betriebsmittel_vorhanden = "SELECT * FROM wawi.tbl_betriebsmittel WHERE person_id = ".$db->db_add_param($person_id, FHC_INTEGER)."
							AND betriebsmitteltyp='Zutrittskarte';";
						if($result_betriebsmittel_vorhanden = $db->db_query($qry_betriebsmittel_vorhanden))
						{
							if($row_betriebsmittel_vorhanden = $db->db_fetch_object($result_betriebsmittel_vorhanden))
							{
								$betriebsmittel_id=$row_betriebsmittel_vorhanden->betriebsmittel_id;
								//Betriebsmittel bereits angelegt
							}
							else
							{
								$betriebsmittel_id = '';
								// Betriebsmittel anlegen
								$betriebsmittel = new betriebsmittel();
								$betriebsmittel->new=true;
								$betriebsmittel->betriebsmitteltyp = 'Zutrittskarte';
								if($betriebsmittel->save())
								{
									$statistik_betriebsmittel_insert++;
									$betriebsmittel_id = $betriebsmittel->betriebsmittel_id;
								}
								else
								{
									$statistik_betriebsmittel_error++;
									logMessage("Fehler beim Anlegen des Betriebsmittels aufgetreten: ".$betriebsmittel->errormsg);
								}

								if($betriebsmittel_id != '' && isset($row_student->idcard_valid_from_changeable))
								{
									$betriebsmittelperson = new betriebsmittelperson();
									$betriebsmittelperson->new = true;
									$betriebsmittelperson->person_id = $person_id;
									$betriebsmittelperson->betriebsmittel_id = $betriebsmittelperson_id;
									if($row_student->idcard_valid_from_changeable != '')
									{
										$month = mb_substr($row_student->idcard_valid_from_changeable, 0, 2);
										$year = mb_substr($row_student->idcard_valid_from_changeable, 2, 7);
										$betriebsmittelperson->ausgegebenam = $year.'-'.$month.'-01';
									}

									if($row_student->idcard_valid_to_changeable != '')
									{
										$month = mb_substr($row_student->idcard_valid_to_changeable, 0, 2);
										$year = mb_substr($row_student->idcard_valid_to_changeable, 2, 7);
										$betriebsmittelperson->retouram = $year.'-'.$month.'-01';
									}


									if(!$betriebsmittelperson->save())
									{
										logMessage("Fehler beim Anlegen der Betriebsmittelperson aufgetreten: ".$betriebsmittelperson->errormsg);
									}
								}
							}
						}
                    }
                }
            }

            if($prestudent_id != '')
            {
                $prestudent = new prestudent();
                $prestudent->load($prestudent_id);

                $qry_benutzer = "SELECT * FROM public.tbl_benutzer WHERE person_id =".$db->db_add_param($person_id).';';
                if($result_benutzer = $db->db_query($qry_benutzer))
                {
                    if($row_benutzer = $db->db_fetch_object($result_benutzer))
                    {
                        // Benutzer schon vorhanden
                        $uid = $row_benutzer->uid;
						$benutzer = new benutzer();
						$benutzer->load($uid);

                        $benutzer->new = false;
						$benutzer->person_id = $person_id;
						$benutzer->bnaktiv=true;
                        $alias = explode('@',$row_student->emailschool);
                        $benutzer->alias = $alias[0];
                        $benutzer->aktiv = true; // standard -> wird wenn vorhanden überschrieben
                        if(trim($row_student->status) == 'active' || trim($row_student->status)=='Active')
                            $benutzer->aktiv = true;
                        else if(trim($row_student->status) == 'passive')
                            $benutzer->aktiv= false;

						if($benutzer->save())
						{
							$statistik_benutzer_update++;
						}
						else
						{
							$statistik_benutzer_update_error++;
						}
					}
                    else
                    {
                        $studiengang= new studiengang();
                        $studiengang->load($prestudent->studiengang_kz);

                        $benutzer = new benutzer();
                        $benutzer->new = true;
                        $benutzer->person_id = $person_id;
                        $benutzer->bnaktiv = true;
                        $alias = explode('@',$row_student->emailschool);
                        $benutzer->alias = $alias[0];
                        $benutzer->aktiv = true; // standard -> wird wenn vorhanden überschrieben
                        if(trim($row_student->status) == 'active' || trim($row_student->status)=='Active')
                            $benutzer->aktiv = true;
                        else if(trim($row_student->status) == 'passive')
                            $benutzer->aktiv= false;

                        $benutzer->uid= generateUID($studiengang->kurzbz, mb_substr($row_student->studentnumber, 0, 2), $studiengang->typ, trim($row_student->studentnumber));
                        if($benutzer->save($new=true, false))
                        {
                            $statistik_benutzer_insert++;
                        }
                        else
                        {
                            $statistik_benutzer_insert_error++;
                            logMessage('Fehler beim Speichern des Benutzers aufgetreten: '.$benutzer->errormsg);
                        }

                        // insert in Synctabelle
                        $qry = "INSERT INTO sync.lbs_sync_student_uid(zk_p_studentid_t, uid, matrikelnr)
                        VALUES(".$db->db_add_param($row_student->zk_p_studentid_t).",".
                                $db->db_add_param($benutzer->uid).",".
                                $db->db_add_param($row_student->studentnumber, FHC_INTEGER).");";
                        if(!$db->db_query($qry))
                        {
                            logMessage("Fehler beim Schreiben des Sync Eintrages:".$qry);
                        }

                        $uid = $benutzer->uid;

                    }
                }

                // ist student schon angelegt
                $qry_std = "SELECT * FROM public.tbl_student WHERE student_uid=".$db->db_add_param($uid).';';
                if($result_std = $db->db_query($qry_std))
                {
                    if($row_std = $db->db_fetch_object($result_std))
                    {
                        // student vorhanden
						$student=new student();
						$student->load($row_std->student_uid);
						$student->new=false;

                        if(isset($row_student->changed_by))
                            $student->updatevon = $row_student->changed_by;
                        else
                            $student->updatevon = '';
                        if(isset($row_student->created_by))
                            $student->insertvon = $row_student->created_by;
                        else
                            $student->insertvon = '';
                        $student->matrikelnr = $row_student->studentnumber;
                        $student->uid = $uid;
                        $student->prestudent_id = $prestudent_id;
                        $student->studiengang_kz = $studiengang->studiengang_kz;
                        $student->semester = 0;

						if($student->save())
						{
							$statistik_student_update++;
						}
						else
						{
							$statistik_student_update_error++;
						}
					}
                    else
                    {
                        // neu anlegen
                        $student = new student();
                        //$student->updateamum = $row_student->changed;
                        if(isset($row_student->changed_by))
                            $student->updatevon = $row_student->changed_by;
                        else
                            $student->updatevon = '';
                        //$student->insertamum = $row_student->created;
                        if(isset($row_student->created_by))
                            $student->insertvon = $row_student->created_by;
                        else
                            $student->insertvon = '';
                        $student->matrikelnr = $row_student->studentnumber;
                        $student->uid = $uid;
                        $student->prestudent_id = $prestudent_id;
                        $student->studiengang_kz = $studiengang->studiengang_kz;
                        $student->semester = 0;

                        if($student->save(true, false))
                        {
                            $statistik_student_insert++;
                        }
                        else
                        {
                            $statistik_student_insert_error++;
                            logMessage("Fehler beim Anlegen des Studenten ".$benutzer->uid." -".$student->errormsg);
                        }
                    }
                }


                //Datum wo Student begonnen hat -> Quereinsteiger oder Normal wissen wir nicht
                $arrival_date = trim($row_student->dateofarrival);

                if(mb_ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})",$row_student->dateofarrival, $regs))
                {
                    $arrival_date = date('d.m.Y', mktime(0,0,0,$regs[1],$regs[2],$regs[3]));
                }


                $qry_status = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id =".$db->db_add_param($prestudent_id, FHC_INTEGER)."
                    AND status_kurzbz ='Student' AND ausbildungssemester = '0';";
                if($result_status = $db->db_query($qry_status))
                {
                    if($row_status = $db->db_fetch_object($result_status))
                    {
                        // Status bereits vorhanden
                    }
                    else
                    {
                        // Prestudentstatus speichern
                        $prestudent->status_kurzbz = 'Student';
                        $prestudent->ausbildungssemester = '0';
                        $prestudent->datum = $datum->formatDatum(trim($arrival_date),'Y-m-d');
                        $prestudent->new = true;
                        $sem = new studiensemester();
                        $prestudent->studiensemester_kurzbz = $sem->getSemesterFromDatum($prestudent->datum);
                        if($prestudent->studiensemester_kurzbz == '')
                            $prestudent->studiensemester_kurzbz = 'WS2014';
                        if($prestudent->save_rolle())
                        {
                            $statistik_status_insert++;
                        }
                        else
                        {
                            $statistik_status_error++;
                            logMessage("Fehler beim Anlegen des Prestudentenstatus - ".$prestudent->errormsg.' Student');
                        }
                    }
                }
                else
                {
                    logMessage('Fehler bei der Abfrage zum prestudentstatus aufgetreten');
                }


                // Absolvent anlegen
                if($row_student->statusdetail == 3)
                {
                    $qry_status = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id =".$db->db_add_param($prestudent_id, FHC_INTEGER)."
                        AND status_kurzb ='Absolvent'";
                    if($result_status = $db->db_query($qry_status))
                    {
                        if($row_status = $db->db_fetch_object($result_status))
                        {
                            // Status bereits vorhanden
                        }
                        else
                        {
                            // neu anlegen
                            $prestudent->status_kurzbz = 'Absolvent';
                            $prestudent->ausbildungssemester=$data_array_stg[$studiengang_kz];
                            $prestudent->datum = '';
                            $prestudent->new = true;
                            $prestudent->studiensemester_kurzbz = 'WS2014';
                            if($prestudent->save_rolle())
                            {
                                $statistik_status_insert++;
                            }
                            else
                            {
                                $statistik_status_error++;
                                logMessage("Fehler beim Anlegen des Prestudentenstatus - ".$prestudent->errormsg.' Absolvent');
                            }
                        }
                    }
                }

                // Abbrecher anlegen
                if($row_student->statusdetail == 4)
                {
                    $qry_statu = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id =".$db->db_add_param($prestudent_id, FHC_INTEGER)."
                        AND status_kurzb ='Abbrecher'";
                    if($result_status = $db->db_query($qry_status))
                    {
                        if($row_status = $db->db_fetch_object($result_status))
                        {
                            // Status bereits vorhanden
                        }
                        else
                        {
                            // neu anlegen
                            $prestudent->status_kurzbz = 'Abbrecher';
                            $prestudent->ausbildungssemester='0';
                            $prestudent->datum = '';
                            $prestudent->new = true;
                            $prestudent->studiensemester_kurzbz = 'WS2014';
                            if($prestudent->save_rolle())
                            {
                                $statistik_status_insert++;
                            }
                            else
                            {
                                $statistik_status_error++;
                                logMessage("Fehler beim Anlegen des Prestudentenstatus - ".$prestudent->errormsg.' Abbrecher');
                            }
                        }
                    }
                }
                // Unterbrecher anlegen
                if($row_student->statusdetail == 2)
                {
                    $qry_statu = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id =".$db->db_add_param($prestudent_id, FHC_INTEGER)."
                        AND status_kurzb ='Unterbrecher'";
                    if($result_status = $db->db_query($qry_status))
                    {
                        if($row_status = $db->db_fetch_object($result_status))
                        {
                            // Status bereits vorhanden
                        }
                        else
                        {
                            // neu anlegen
                            $prestudent->status_kurzbz = 'Unterbrecher';
                            $prestudent->ausbildungssemester='0';
                            $prestudent->datum = '';
                            $prestudent->new = true;
                            $prestudent->studiensemester_kurzbz = 'WS2014';
                            if($prestudent->save_rolle())
                            {
                                $statistik_status_insert++;
                            }
                            else
                            {
                                $statistik_status_error++;
                                logMessage("Fehler beim Anlegen des Prestudentenstatus - ".$prestudent->errormsg.' Unterbrecher');
                            }
                        }
                    }
                }

                // Lehrveranstaltung anlegen für Projektarbeiten
                $qry_stg = "SELECT 1 FROM lehre.tbl_lehrveranstaltung
                    WHERE bezeichnung ='Projektarbeit'
                    AND studiengang_kz =".$db->db_add_param($prestudent->studiengang_kz,FHC_INTEGER).";";

                if($result_stg = $db->db_query($qry_stg))
                {
                    if($row_stg = $db->db_fetch_object($result_stg))
                    {
                        // die lv ist bereits angelegt
                        $lv_id = $row_stg->lehrveranstaltung_id;
                    }
                    else
                    {
                        $lv = new lehrveranstaltung();
                        $lv->semester = 0;
                        $lv->bezeichnung = 'Projektarbeit';
                        $lv->studiengang_kz = $prestudent->studiengang_kz;
                        $lv->kurzbz = 'PROJ';
                        $lv->new = true;

                        if(!$lv->save())
                            logMessage("Fehler beim Anlegen der LV im Studiengang ".$studiengang_kz.": ".$lv->errormsg);

                        $lv_id = $lv->lv_id;

                        $lehrfach = new lehrfach();
                        $lehrfach->new = true;
                        $lehrfach->studiengang_kz = $prestudent->studiengang_kz;
                        $lehrfach->fachbereich_kurzbz = 'Dummy';
                        $lehrfach->bezeichnung = 'Projektarbeit';
                        $lehrfach->kurzbz = 'PROJ';
                        $lehrfach->aktiv = true;
                        $lehrfach->sprache = 'German';

                        if(!$lehrfach->save())
                            logMessage("Fehler beim Anlegen des Lehrfaches im Studiengang ".$studiengang_kz.": ".$lehrfach->errormsg);
                    }
                }

                $semester_projekt = '';
                if($row_student->anfang_ppt != '')
                {
                    // Semester von beginn suchen
                    $studiensemester = new studiensemester();
                    $datum_sem= '';
                    if(mb_ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})",$row_student->anfang_ppt, $regs))
                    {
                        $datum_sem = date('Y-m-d', mktime(0,0,0,$regs[1],$regs[2],$regs[3]));
                    }

                    if($datum_sem != '')
                    {
                        $semester_projekt = $studiensemester->getSemesterFromDatum($datum_sem);
                    }
                    else
                    {
                        logMessage("Warnung: Fehler beim Parsen des Projekt-Anfangdatums: ".$row_student->anfang_ppt);
                    }
                }
                else
                {
                    // Semester von 1. Studentenstatus holen
                    if($row_student->dateofarrival != '')
                    {
                        // Datum ist in 2 verschiedenen formaten vorhanden
                        $datum_sem = '';
                        $studiensemester = new studiensemester();

                        if(mb_ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})",$row_student->dateofarrival, $regs))
                        {
                            $datum_sem = date('Y-m-d', mktime(0,0,0,$regs[1],$regs[2],$regs[3]));
                        }

                        // regular expression hat nicht gegriffen
                        if($datum_sem =='')
                        {
                            $datum = new datum();
                            $datum_sem = $datum->formatDatum($row_student->dateofarrival, 'Y-m-d');
                        }
                        if($datum_sem != '')
                        {
                            $semester_projekt = $studiensemester->getSemesterFromDatum($datum_sem);
                        }
                        else
                        {
                            logMessage("Warnung: Fehler beim Parsen des Projekt-Anfangdatums: ".$row_student->anfang_ppt);
                        }
                    }
                }

                // projektarbeit anlegen
                if($semester_projekt == '')
                {
                    $semester_projekt = 'WS2014';
                }

                $le_id = '';
                // gibt es lehreinheit für dieses semester schon
                $qry_le = 'SELECT * FROM lehre.lehreinheit WHERE studiensemester_kurzb ='.$db->db_add_param($semester_projekt, FHC_STRING).';';
                if($result_le = $db->db_query($qry_le))
                {
                    if($row_le = $db->db_fetch_object($result_le))
                    {
                        // lehreinheit gibt es schon
                        $le_id = $row_le->lehreinheit_id;
                    }
                    else
                    {
                        // lehreinheit neu anlegen
                        $lehreinheit = new lehreinheit();
                        $lehreinheit->studiensemester_kurzbz = $semester_projekt;
                        $lehreinheit->lehrfach = $lehrfach->lehrfach_id;
                        $lehreinheit->lehrveranstaltung_id = $lv_id;
                        if($lehreinheit->save())
                        {
                            $le_id = $lehreinheit->lehreinheit_id;
                        }
                    }
                }

				//Firma anlegen für Projektbetreuer
				$qry_firma="SELECT * FROM public.tbl_firma WHERE name =".trim($row_student->unternehmen).";";
				if($result_firma=$db->db_query($qry_firma))
				{
					if($row_firma=$db->db_fetch_object($result_firma))
					{
						//Firma bereits vorhanden
						$firma_id=$row_firma->firma_id;
					}
					else
					{
						$firma=new firma();
						$firma->new=true;
						$firma->name=trim($row_student->unternehmen);
						$firma->schule=false;
						$firma->aktiv=true;
						$firma->gesperrt=false;
						$firma->firmentyp='Partnerfirma';
						$firma->insertamum = date('Y-m-d H:i:s');
						$firma->insertvon='Sync';
						$firma->updateamum = date('Y-m-d H:i:s');
						$firma->updatevon='Sync';

						if($firma->save())
						{
							$statistik_firma_insert++;
							$firma_id=$firma->firma_id;
						}
						else
						{
							$statistik_firma_insert_error++;
						}

						sync_firma_kontakt($firma_id,'telefon',trim($row_student->telefon));

						if($firma_id!='')
						{
							if($row_student->telefon != '')
								sync_firma_kontakt($firma_id,'telefon',$row_student->telefon);

							$adresse=new adresse();
							$adresse->new=true;
							$adresse->strasse=trim($row_student->strasse_gasse_platz);
							$adresse->heimatadresse=true;
							$adresse->zustelladresse=true;
							$adresse->ort=trim($row_student->stadt_ort);
							$adresse->firma_id=$firma_id;
							$adresse->insertamum = date('Y-m-d H:i:s');
							$adresse->insertvon='Sync';
							$adresse->updateamum = date('Y-m-d H:i:s');
							$adresse->updatevon='Sync';

							if($adresse->save())
							{
								$statistik_adresse_insert++;
							}
							else
							{
								$statistik_adresse_insert_error++;
							}
						}
					}
				}

				//TODO: Abfrage, ob Projektarbeit schon angelegt wurde
				// projektarbeit speichern
				if($le_id != '')
				{
					$student = new student($uid);

					$projektarbeit = new projektarbeit();
					$projektarbeit->new=true;
					$projektarbeit->projekttyp_kurzbz='Praktikum';
					$projektarbeit->lehreinheit_id=$le_id;
					$projektarbeit->prestudent_id=$student->prestudent_id;
					$projektarbeit->firma_id=$firma_id;
                    $projektarbeit->beginn = $datum_sem;
                    $projektarbeit->ende = $row_student->ende_ppt;
					$projektarbeit->themenbereich=trim($row_student->abteilung_position);
					$projektarbeit->updateamum = date('Y-m-d H:i:s');
					$projektarbeit->updatevon = 'Sync';
					$projektarbeit->insertamum = date('Y-m-d H:i:s');
					$projektarbeit->insertvon = 'Sync';

					if($projektarbeit->save())
					{
						$statistik_projektarbeit_insert++;
						$projektarbeit_id=$projektarbeit->projektarbeit_id;
					}
					else
					{
						$statistik_projektarbeit_insert_error++;
					}
				}

				//Projektbetreuer anlegen
				if($row_student->betreuer_im_unternehmen != '' && $projektarbeit_id != '')
				{
					$person = new person();
					$person->new=true;
					$person->name=trim($row_student->betreuer_im_unternehmen);
					$person->geschlecht='u';
					$person->aktiv=true;
					$person->insertamum = date('Y-m-d H:i:s');
					$person->insertvon='Sync';
					$person->updateamum = date('Y-m-d H:i:s');
					$person->updatevon='Sync';

					if($person->save())
					{
						$statistik_person_insert++;
						$betreuer_person_id=$person->person_id;
					}
					else
					{
						$statistik_person_insert_error++;
					}

					if($betreuer_person_id != '')
					{
						if($row_student->email_betreuer != '')
							sync_kontakt($betreuer_person_id, 'email', trim($row_student->email_betreuer));
						if($row_student->telefon_betreuer != '')
							sync_kontakt($betreuer_person_id, 'telefon', trim($row_student->telefon_betreuer));

						$betreuer=new projektbetreuer();
						$betreuer->new=true;
						$betreuer->person_id=$betreuer_person_id;
						$betreuer->name=trim($row_student->betreuer_im_unternehmen);
						$betreuer->betreuerart_kurzbz='Betreuer';
						$betreuer->projektarbeit_id=$projektarbeit_id;
						$betreuer->insertamum = date('Y-m-d H:i:s');
						$betreuer->insertvon='Sync';
						$betreuer->updateamum = date('Y-m-d H:i:s');
						$betreuer->updatevon='Sync';
					}
				}
            }
        }
    }

    logMessage("----- Studiengang ".$studiengang_kz." syncronisiert-------");
    logMessage("Update vorhandener Personen: ".$statistik_person_update);
    logMessage("Fehler bei Update vorhandener Personen: ".$statistik_person_update_error);
    logMessage("Update vorhandener Prestudenten: ".$statistik_prestudent_update);
    logMessage("Fehler bei Update vorhandener Prestudenten: ".$statistik_prestudent_update_error);
    logMessage("Neue Personen hinzugefügt: ".$statistik_person_insert);
    logMessage("Fehler bei Personen hinzufügen: ".$statistik_person_insert_error);
    logMessage("Neue Prestudenten hinzugefügt: ".$statistik_prestudent_insert);
    logMessage("Fehler bei Prestudent hinzufügen: ".$statistik_prestudent_insert_error);
    logMessage("Neue Kontakte: ".$statistik_kontakt_insert);
    logMessage("Fehler bei Kontakten: ".$statistik_kontakt_error);
    logMessage("Neue Adressen: ".$statistik_adresse_insert);
    logMessage("Fehler bei Adressen: ".$statistik_adresse_insert_error);
    logMessage("Neue Benutzer hinzugefügt: ".$statistik_benutzer_insert);
    logMessage("Fehler bei Benutzer hinzufügen: ".$statistik_benutzer_insert_error);
    logMessage("Neue Studenten hinzugefügt: ".$statistik_student_insert);
    logMessage("Fehler bei Studenten hinzufügen: ".$statistik_student_insert_error);
    logMessage("Neue Prestudentenstati hinzugefügt: ".$statistik_status_insert);
    logMessage("Fehler bei Prestudentenstati hinzufügen: ".$statistik_status_error);
    logMessage("Neue Betriebsmittel hinzugefügt: ".$statistik_betriebsmittel_insert);
    logMessage("Fehler bei Betriebsmittel hinzufügen: ".$statistik_betriebsmittel_error);

}


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
	global $db, $statistik_kontakt_insert, $statistik_kontakt_error;
	global $statistik_kontakt_aktualisiert;

	if($kontakt!='')
	{
		$qry = "SELECT 1 FROM public.tbl_kontakt WHERE kontakttyp=".$db->db_add_param($kontakttyp)." AND person_id=".$db->db_add_param($person_id, FHC_INTEGER);

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
						$kontakt_obj->kontakttyp=$kontakttyp;
						$kontakt_obj->person_id=$person_id;
						$kontakt_obj->zustellung=false;
						$kontakt_obj->updateamum = date('Y-m-d H:i:s');
						$kontakt_obj->updatevon='Sync';

						if($kontakt_obj->save())
						{
							$statistik_kontakt_aktualisiert++;
							logMessage("Aktualisiere Kontakt von $row_lecturer->namelast ($person_id) Kontakttyp $kontakttyp von $row_kontakt->kontakt auf $kontakt ");
						}
						else
						{
							$statistik_kontakt_error++;
							logMessage("Fehler PersonID $person_id Kontakttyp $kontakttyp: $kontakt_obj->errorsmg");
						}
					}
				}
			}
			else
			{
				//Noch nicht vorhanden
				$kontakt_obj = new kontakt();
				$kontakt_obj->kontakttyp=$kontakttyp;
				$kontakt_obj->kontakt=$kontakt;
				$kontakt_obj->new = true;
				$kontakt_obj->person_id=$person_id;
				$kontakt_obj->zustellung=false;
				$kontakt_obj->insertamum = date('Y-m-d H:i:s');
				$kontakt_obj->insertvon='Sync';
				$kontakt_obj->updateamum = date('Y-m-d H:i:s');
				$kontakt_obj->updatevon='Sync';

				if($kontakt_obj->save())
				{
					$statistik_kontakt_insert++;
				}
				else
				{
					$statistik_kontakt_error++;
					logMessage("Fehler PersonID $person_id Kontakttyp $kontakttyp: $kontakt_obj->errormsg");
				}
			}
		}
	}
}

function sync_firma_kontakt($firma_id, $kontakttyp, $kontakt)
{
	global $db, $statistik_kontakt_insert, $statistik_kontakt_error;
	global $statistik_kontakt_aktualisiert;

	if($kontakt!='')
	{
		$qry = "SELECT 1 FROM public.tbl_kontakt WHERE kontakttyp=".$db->db_add_param($kontakttyp)." AND firma_id=".$db->db_add_param($firma_id, FHC_INTEGER);

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
						$kontakt_obj->kontakttyp=$kontakttyp;
						$kontakt_obj->firma_id=$firma_id;
						$kontakt_obj->zustellung=false;
						$kontakt_obj->updateamum = date('Y-m-d H:i:s');
						$kontakt_obj->updatevon='Sync';

						if($kontakt_obj->save())
						{
							$statistik_kontakt_aktualisiert++;
							logMessage("Aktualisiere Kontakt von $row_lecturer->namelast ($person_id) Kontakttyp $kontakttyp von $row_kontakt->kontakt auf $kontakt ");
						}
						else
						{
							$statistik_kontakt_error++;
							logMessage("Fehler PersonID $person_id Kontakttyp $kontakttyp: $kontakt_obj->errorsmg");
						}
					}
				}
			}
			else
			{
				//Noch nicht vorhanden
				$kontakt_obj = new kontakt();
				$kontakt_obj->kontakttyp=$kontakttyp;
				$kontakt_obj->kontakt=$kontakt;
				$kontakt_obj->new = true;
				$kontakt_obj->firma_id=$firma_id;
				$kontakt_obj->zustellung=false;
				$kontakt_obj->insertamum = date('Y-m-d H:i:s');
				$kontakt_obj->insertvon='Sync';
				$kontakt_obj->updateamum = date('Y-m-d H:i:s');
				$kontakt_obj->updatevon='Sync';

				if($kontakt_obj->save())
				{
					$statistik_kontakt_insert++;
				}
				else
				{
					$statistik_kontakt_error++;
					logMessage("Fehler PersonID $person_id Kontakttyp $kontakttyp: $kontakt_obj->errormsg");
				}
			}
		}
	}
}



?>
