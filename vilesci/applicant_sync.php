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
require_once('../../../include/datum.class.php'); 
require_once('../../../include/person.class.php'); 
require_once('../../../include/adresse.class.php'); 
require_once('../../../include/prestudent.class.php'); 
require_once('../../../include/kontakt.class.php'); 
require_once('../../../include/konto.class.php'); 
require_once('../../../include/studiengang.class.php'); 
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/functions.inc.php'); 
require_once('../../../include/projektarbeit.class.php');
require_once('../../../include/dokument.class.php');
// addon klasse
require_once('../../kompetenzen/kompetenz.class.php'); 

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

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$datum = new datum();

if(!$rechte->isBerechtigt('addon/lbsdatenmigration'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$data_array_geschlecht=array(
	"female"=>"w",
	"male"=>"m",
	"" => "u");

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
    "H"=>"H",
    "Czech"=>"TCH",
    "Peru"=>"PE",
    "Z"=>"Z",
    "Bosnia-Herzegovina"=>"BSH",
    "Kyrgyzstan"=>"KRG",
    "Spain"=>"E",
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
    "Latvia"=>"LLD",
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
    "Kenya"=>"EAK",
    "Kazakhstan"=>"KAS",
    "Macedonian"=>"MAZ",
    "Uruguay"=>"U",
    "Greece"=>"GR",
    "RUSSIA"=>"RSF",
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
    "MOLDAVIA"=>"MLD",
    "Republic of Moldova"=>"MLD",
    "ALBANIA"=>"AL",
    "SLOVENIA"=>"SLO",
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
    "Israel/Poland"=>"IL",
    "Stockholm"=>"S",
    "Hannover"=>"D",
    "Europort, Gibraltar"=>"",
	""=>null);

$db = new basis_db();
$logfile='applicant_sync.log';

// Logfile oeffnen
if(!$loghandle=fopen($logfile, 'w'))
	die("Kann Logfile $logfile nicht öffnen!");

logMessage("Starte Datenuebernahme");


$statistik_neue_personen=0;
$statistik_gefundene_personen=0;
$statistik_warnung=0;
$statistik_adresse_neu=0;
$statistik_adresse_fehler=0;
$statistik_konto = 0;
$statistik_konto_fehler = 0;
$statistik_prestudent = 0; 
$statistik_prestudent_fehler= 0; 
$statistik_status_insert =0;
$statistik_status_error=0;
$statistik_person_update= 0; 
$statistik_person_update_fehler = 0; 
$statistik_adresse_update=0;
$statistik_adresse_update_fehler=0;
$statistik_gefundene_adressen=0;
$statistik_prestudent_update=0;
$statistik_prestudent_update_fehler=0;
$statistik_gefundene_prestudenten=0;
$statistik_dokumente_neu=0;
$statistik_dokumente_fehler=0;
$statistik_kompetenz_insert =0; 
$statistik_kompetenz_insert_error=0; 

//Alle Einträge in der Tabelle lbs_applicant durchlaufen
$qry_applicant = "SELECT * FROM sync.lbs_applicant";
if($result_applicant = $db->db_query($qry_applicant))
{
	while($row_applicant = $db->db_fetch_object($result_applicant))
	{
        $person_id = '';
        $error = false;
        
        // ist Bewerber schon angelegt (check auf Zwischentabelle)
        if($row_applicant->zk_p_applicantid_t!='')
			$qry_person = "SELECT * FROM sync.lbs_sync_applicant_person WHERE zk_p_applicantid_t=".$db->db_add_param($row_applicant->zk_p_applicantid_t);
		else
			$qry_person = "SELECT * FROM sync.lbs_sync_applicant_person WHERE zk_p_lecturerid_t is null";
        
        if($result_person = $db->db_query($qry_person))
		{
			if($row_person = $db->db_fetch_object($result_person))
			{
				// Personendatensatz wurde gefunden
				$person_id = $row_person->person_id;
			}
			else
			{
                if($row_applicant->dateofbirth == '')
                {
                    $gebdatum = ''; 
                    logMessage("Warnung: ".$row_applicant->namefirst.' '.$row_applicant->namelast." hat kein Geburtsdatum"); 
                }
				elseif(mb_ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})",$row_applicant->dateofbirth, $regs))
				{
					$gebdatum = date('Y-m-d', mktime(0,0,0,$regs[1],$regs[2],$regs[3]));
				}
				else
				{
					logMessage("Error beim Parsen des Geburtsdatum: ".$row_applicant->dateofbirth);
				}
                
                // SVNR ist Teilweise nur 4 stellig gespeichert und muss mit geburtsdatum erweitert werden
				if(mb_strlen($row_applicant->svnumber)==4)
					$svnr = trim($row_applicant->svnumber).$datum->formatDatum($gebdatum,'dmy');
				else
					$svnr = trim($row_applicant->svnumber);
                
                if($svnr == '')
                    $svnr = $row_applicant->erskz; 
                
				// Muell aus der SVNR filtern
				$svnr = str_replace(" ","",$svnr);
				$svnr = str_replace(";","",$svnr);

				// Wenn die SVNR nicht 10 Zeichen lang ist wird diese geleert da sie falsch ist
				if(mb_strlen($svnr)!=10)
				{
					
					logMessage("Warnung SVNR von $row_applicant->namelast $row_applicant->namefirst ($row_applicant->zk_p_applicantid_t) ist ungueltig ");
					$svnr='';
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
        }
        
        if($person_id != '')
        {
            // update
            $person = new person(); 
            $person->load($person_id); 
            
            $person->nachname = ucfirst(mb_strtolower($row_applicant->namelast)); 
            $person->vorname = trim($row_applicant->namefirst); 
            $person->gebdatum = $gebdatum; 
            $person->anmerkung = trim($row_applicant->notesgeneral); 
            if($row_applicant->visastatus != '')
                $person->anmerkung.= ' Visastatus: '.$row_applicant->visastatus; 
            $person->svnr = $svnr; 
            $person->aktiv = true; 
            $person->new = false; 
            $person->ersatzkennzeichen = trim($row_applicant->erskz); 
            $person->staatsbuergerschaft = $data_array_nationen[$row_applicant->citizenship];
            $person->geschlecht = $data_array_geschlecht[$row_applicant->sex]; 
            $person->gebort = trim($row_applicant->birthplace); 
            $person->geburtsnation = (isset($data_array_nationen[$row_applicant->birthplace])?$data_array_nationen[$row_applicant->birthplace]:'');
            $person->insertvon = trim($row_applicant->createdby);
            $person->updatevon = trim($row_applicant->changedby); 
            
            if($person->save())
            {
                $statistik_person_update++; 
            }
            else
            {
                $statistik_person_update_fehler++;
                logMessage('Update von person '.$person_id.' fehlgeschlagen '.$person->errormsg); 
            }
            
            // Kompetenz Update
            
            $kompetenz = new kompetenz(); 
            $kompetenz->getKompetenzPerson($person_id); 
            
            if($row_applicant->language_german != '')
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
					$kompetenz->kompetenzniveau = $row_applicant->language_german; 
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
				
					switch ($row_applicant->language_german)
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
            
            if($row_applicant->language_french != '')
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
					$kompetenz->kompetenzniveau = $row_applicant->language_french; 
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
					switch ($row_applicant->language_french)
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
            
            if($row_applicant->language_hebrew != '')
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
					$kompetenz->kompetenzniveau = $row_applicant->language_hebrew; 
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
					
					switch ($row_applicant->language_hebrew)
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
            
            if($row_applicant->language_russian != '')
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
					$kompetenz->kompetenzniveau = $row_applicant->language_russian; 
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
					
					switch ($row_applicant->language_russian)
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
            
            if($row_applicant->language_spain != '')
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
					$kompetenz->kompetenzniveau = $row_applicant->language_spain; 
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
					
					switch ($row_applicant->language_spain)
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
            
            $statistik_gefundene_personen++;
        }
        else
        {
            // neue Person anlegen
            $person = new person(); 
            
            $person->nachname = ucfirst(mb_strtolower(trim($row_applicant->namelast))); 
            $person->vorname = trim($row_applicant->namefirst); 
            $person->gebdatum = $gebdatum; 
            $person->anmerkung = trim($row_applicant->notesgeneral);
            if($row_applicant->visastatus != '')
                $person->anmerkung.= ' Visastatus: '.$row_applicant->visastatus; 
            $person->svnr = $svnr; 
            $person->aktiv = true; 
            $person->new = true; 
            $person->ersatzkennzeichen = trim($row_applicant->erskz); 
            $person->staatsbuergerschaft = $data_array_nationen[$row_applicant->citizenship];
            $person->geschlecht = $data_array_geschlecht[$row_applicant->sex]; 
            $person->gebort = trim($row_applicant->birthplace); 
            $person->geburtsnation = (isset($data_array_nationen[$row_applicant->birthplace])?$data_array_nationen[$row_applicant->birthplace]:'');
            $person->insertvon = trim($row_applicant->createdby);
            $person->updatevon = trim($row_applicant->changedby); 
            
            if($person->save())
            {
                $person_id = $person->person_id; 
                $statistik_neue_personen++;
                
                $qry = "INSERT INTO sync.lbs_sync_applicant_person(zk_p_applicantid_t,person_id) 
                        VALUES(".$db->db_add_param($row_applicant->zk_p_applicantid_t).",".
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
                //logMessage($person->person_id);
                $error = true;
            }
			
            if(!$error)
			{
				// Kompetenzen anlegen
				// wenn vorhanden kompetenztyp anlegen
				if($row_applicant->language_french != '')
				{
					$kompetenz = new kompetenz(); 
					$kompetenz->kompetenztyp_id = '6';
					$kompetenz->person_id = $person_id; 
					$kompetenz->kompetenzniveau = $row_applicant->language_french;
					$kompetenz->insertamum = date('Y-m-d H:i:s');
					$kompetenz->insertvon = 'Sync';
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
					switch ($row_applicant->language_french)
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
				
				if($row_applicant->language_german != '')
				{
					$kompetenz = new kompetenz(); 
					$kompetenz->kompetenztyp_id = '2';
					$kompetenz->person_id = $person_id; 
					$kompetenz->kompetenzniveau = $row_applicant->language_german; 
					$kompetenz->insertamum = date('Y-m-d H:i:s');
					$kompetenz->insertvon = 'Sync';
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
					
					switch ($row_applicant->language_german)
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
				
				if($row_applicant->language_hebrew != '')
				{
					$kompetenz = new kompetenz(); 
					$kompetenz->kompetenztyp_id = '3';
					$kompetenz->person_id = $person_id; 
					$kompetenz->kompetenzniveau = $row_applicant->language_hebrew; 
					$kompetenz->insertamum = date('Y-m-d H:i:s');
					$kompetenz->insertvon = 'Sync';
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
					
					switch ($row_applicant->language_hebrew)
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
				
				if($row_applicant->language_russian != '')
				{
					$kompetenz = new kompetenz(); 
					$kompetenz->kompetenztyp_id = '4';
					$kompetenz->person_id = $person_id; 
					$kompetenz->kompetenzniveau = $row_applicant->language_russian; 
					$kompetenz->insertamum = date('Y-m-d H:i:s');
					$kompetenz->insertvon = 'Sync';
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
					
					switch ($row_applicant->language_russian)
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
				
				if($row_applicant->language_spain != '')
				{
					$kompetenz = new kompetenz(); 
					$kompetenz->kompetenztyp_id = '5';
					$kompetenz->person_id = $person_id; 
					$kompetenz->kompetenzniveau = $row_applicant->language_spain; 
					$kompetenz->insertamum = date('Y-m-d H:i:s');
					$kompetenz->insertvon = 'Sync';
					$kompetenz->updateamum = date('Y-m-d H:i:s');
					$kompetenz->updatevon = 'Sync';
                    $kompetenz->new = true; 
					
					switch ($row_applicant->language_spain)
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

				// Buchungen die Student gezahlt hat anlegen
                $qry ="SELECT * From sync.lbs_applicantpayment WHERE zk_f_applicantid_t =".$db->db_add_param($row_applicant->zk_p_applicantid_t)."
                    AND zk_p_applicant_paymentid_t NOT IN (SELECT zk_p_applicant_paymentid_t from sync.lbs_sync_applicant_payment);";
                
                if($result_buchung = $db->db_query($qry))
                {
                    while($row_betrag = $db->db_fetch_object($result_buchung))
                    {
                        $konto = new konto(); 
                        $konto->new = true; 
                        $konto->betrag = trim($row_betrag->amount); 
                        $konto->buchungstext = trim($row_betrag->amountnote); 
                        $konto->buchungsdatum = $datum->formatDatum(trim($row_betrag->amountdate),'Y-m-d');
                        //$konto->ext_id = trim($row_betrag->zk_p_applicant_paymentid_t); 
                        $konto->person_id = $person_id; 
                        $konto->buchungstyp_kurzbz = ($row_betrag->amount == '363.63')?'Studiengebuehr':'Sonstiges';
                        $konto->mahnspanne = '30'; 
                        if($konto->buchungsdatum != '')
                            $konto->studiensemester_kurzbz = getStudiensemesterFromDatum($datum->formatDatum(trim($row_betrag->amountdate),'Y-m-d'));
                        else
                        {
                            logMessage("WARNUNG: Zahlung hat kein gültiges Datum für Person ".$person_id); 
                            $konto->studiensemester_kurzbz = 'WS2016'; 
                        }
                        
                        $konto->studiengang_kz= ''; 
                        if($row_applicant->chosen_studyprogram_year != '')
                        {
                            $studiengang_kz = strtolower(mb_substr($row_applicant->chosen_studyprogram_year, 0,-1)); 
                            $qry_stg = "SELECT studiengang_kz FROM public.tbl_studiengang WHERE kurzbz=".$db->db_add_param($studiengang_kz).';';
                            if($result_stg = $db->db_query($qry_stg))
                            {
                                if($row_stg = $db->db_fetch_object($result_stg))
                                {
                                    $konto->studiengang_kz=$row_stg->studiengang_kz;
                                }
                            }
                        }
                        
                        if($konto->save())
                        {
                            $buchungsnr = $konto->buchungsnr;
                            $statistik_konto++; 
                            
                            $qry = "INSERT INTO sync.lbs_sync_applicant_payment(zk_p_applicant_payment_id_t, zk_f_applicantid_t, buchungsnr) 
                            VALUES(".$db->db_add_param($row_betrag->zk_p_applicant_paymentid_t).",".
                                    $db->db_add_param($row_applicant->zk_p_applicantid_t).",".
                                    $db->db_add_param($buchungsnr, FHC_INTEGER).");";
                            if(!$db->db_query($qry))
                            {
                                logMessage("Fehler beim Schreiben des Sync Eintrages:".$qry);
                            }
                            
                        }
                        else
                        {
                            logMessage("Fehler beim Anlegen des Kontoeintrages:".$konto->errormsg);
                            $statistik_konto_fehler++;
                        }

                    }
                }

                // Belastungsbuchungen
                $amount_array = explode('', $row_applicant->paymentamount); 
                $year_array = explode('', $row_applicant->paymentamountyear); 
                $count =0;
                foreach($amount_array as $amount)
                {
                    if($amount_array[$count]!='' && $amount_array[$count]!='')
                    {
                        $konto = new konto(); 
                        $konto->new = true; 
                        $konto->betrag = '-'.trim($amount_array[$count]); 
                        //$konto->buchungstext = trim($row_betrag->amountnote); 
                        //$konto->buchungsdatum = $datum->formatDatum(trim($row_betrag->amountdate),'Y-m-d');
                        //$konto->ext_id = trim($row_betrag->zk_p_applicant_paymentid_t); 
                        $konto->person_id = $person_id; 
                        $konto->buchungstyp_kurzbz = ($amount_array[$count] == '363.63')?'Studiengebuehr':'Sonstiges';
                        $konto->mahnspanne = '30'; 
                        if($year_array[$count] != '')
                        {
                            $konto->studiensemester_kurzbz = 'WS'.mb_substr($year_array[$count], 0,4);
                        }   
                        else
                        {
                            logMessage("WARNUNG: Zahlung hat kein gültiges Datum für Person ".$person_id); 
                            $konto->studiensemester_kurzbz = 'WS2010'; 
                        }

                        $konto->studiengang_kz= ''; 
                        if($row_applicant->chosen_studyprogram_year != '')
                        {
                            $studiengang_kz = strtolower(mb_substr($row_applicant->chosen_studyprogram_year, 0,-1)); 
                            $qry_stg = "SELECT studiengang_kz FROM public.tbl_studiengang WHERE kurzbz=".$db->db_add_param($studiengang_kz).';';
                            if($result_stg = $db->db_query($qry_stg))
                            {
                                if($row_stg = $db->db_fetch_object($result_stg))
                                {
                                    $konto->studiengang_kz=$row_stg->studiengang_kz;
                                }
                            }
                        }

                        if($konto->save())
                        {
                            $statistik_konto++; 
                        }
                        else
                        {
                            logMessage("Fehler beim Anlegen des Kontoeintrages:".$konto->errormsg.' '.$konto->betrag);
                            $statistik_konto_fehler++;
                        }
                    }
                    $count++; 
                }
            }
        }
        
        if($person_id != '')
        {
            // Hauptwohnsitzadresse übernehmen wenn nicht leer
            if($row_applicant->homeaddressline1 != '' || $row_applicant->homeaddressline2 != '' || $row_applicant->homeaddresscity != '' || 
                    $row_applicant->homeaddressstate != '' || $row_applicant->homeaddresszip != '')
            {
                $qry_homeadress = "SELECT * FROM public.tbl_adresse WHERE person_id=".$db->db_add_param($person_id)." AND typ='h'";
                if($result_homeadress = $db->db_query($qry_homeadress))
                {      
                    if($row_homeadress = $db->db_fetch_object($result_homeadress))
                    {
                        // Datensatz schon vorhanden
                        // Update
						$adresse = new adresse();
						$adresse->load($row_homeadress->adresse_id);
						$adresse->new = false;
						
						$adresse->typ = 'h'; 
                        $adresse->heimatadresse = true; 
                        $adresse->zustelladresse=true;
                        $adresse->firma_id=null; 
                        $adresse->new = false; 
                        $adresse->person_id = $person_id; 
                        $adresse->strasse = trim($row_applicant->homeaddressline1.' '.$row_applicant->homeaddressline2);
                        $adresse->ort = trim($row_applicant->homeaddresscity); 
                        $adresse->plz = trim($row_applicant->homeaddresszip); 
                        $adresse->nation = $data_array_nationen[$row_applicant->homeaddressstate];
						
						if($adresse->save())
						{
							$statistik_adresse_update++;
						}
						else
						{
							$statistik_adresse_update_fehler++;
                            logMessage('Fehler bei adresse update '.$adresse->errormsg); 
						}

						$statistik_gefundene_adressen++;
                    }
                    else
                    {
                        $adresse = new adresse(); 
                        $adresse->new = true; 
                        $adresse->typ = 'h'; 
                        $adresse->heimatadresse = true; 
                        $adresse->zustelladresse=true;
                        $adresse->firma_id=null; 
                        $adresse->person_id = $person_id; 
                        $adresse->strasse = trim($row_applicant->homeaddressline1.' '.$row_applicant->homeaddressline2);
                        $adresse->ort = trim($row_applicant->homeaddresscity); 
                        $adresse->plz = trim($row_applicant->homeaddresszip); 
                        $adresse->nation = $data_array_nationen[$row_applicant->homeaddressstate];
                        
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
            if($row_applicant->viennaaddresscity!='' || $row_applicant->viennaaddressline1!='' ||
                $row_applicant->viennaaddressline2!=''|| $row_applicant->viennaaddresszip!='')
            {

                $qry_adress = "SELECT * FROM public.tbl_adresse WHERE person_id=".$db->db_add_param($person_id)." AND typ='n'";
                if($result_adress = $db->db_query($qry_adress))
                {
                    if($row_adress = $db->db_fetch_object($result_adress))
                    {
                        // Adressendatensatz bereits vorhanden
						$adresse = new adresse();
						$adresse->load($row_adress->adresse_id);
						$adresse->new=false;
						
						$adresse->person_id=$person_id;	
                        $adresse->new = false; 
                        $adresse->plz=trim($row_applicant->viennaaddresszip);
                        $adresse->strasse = trim($row_applicant->viennaaddressline1.' '.$row_applicant->viennaaddressline2);
                        $adresse->ort = trim($row_applicant->viennaaddresscity);
                        $adresse->gemeinde = trim($row_applicant->viennaaddresscity);
                        $adresse->nation = $data_array_nationen[trim($row_applicant->viennaaddresscity)];
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
							$statistik_adresse_update_fehler++;
                            logMessage('Fehler bei adresse update '.$adresse->errormsg); 
						}

						$statistik_gefundene_adressen++;
                    }
                    else
                    {
                        // Adressdatensatz noch nicht vorhanden -> neu anlegen
                        $adresse = new adresse();
                        $adresse->new=true;
                        $adresse->person_id=$person_id;	
                        $adresse->plz=trim($row_applicant->viennaaddresszip);
                        $adresse->strasse = trim($row_applicant->viennaaddressline1.' '.$row_applicant->viennaaddressline2);
                        $adresse->ort = trim($row_applicant->viennaaddresscity);
                        $adresse->gemeinde = trim($row_applicant->viennaaddresscity);
                        $adresse->nation = $data_array_nationen[trim($row_applicant->viennaaddresscity)];
                        $adresse->typ='n';
                        $adresse->heimatadresse=false;
                        $adresse->zustelladresse=false;
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
            sync_kontakt($person_id, 'mobil', $row_applicant->phonemobile);
            sync_kontakt($person_id, 'telefon', $row_applicant->phonehome);
            sync_kontakt($person_id, 'email', $row_applicant->emailprivate); 
            sync_kontakt($person_id, 'emergency', $row_applicant->phoneemergency); 
           
            //Prestudentdaten
            $qry_prestudent = 'SELECT * FROM public.tbl_prestudent WHERE person_id='.$db->db_add_param($person_id, FHC_INTEGER).';'; 
            
            if($result_prestudent = $db->db_query($qry_prestudent))
            {
                if($row_prestudent = $db->db_fetch_object($result_prestudent))
                {
                    // Prestudent schon vorhanden
                    $prestudent_id = $row_prestudent->prestudent_id; 
					$prestudent = new prestudent();
					$prestudent->load($prestudent_id);
					
					$prestudent->ext_id = trim($row_applicant->zk_p_applicantid_t); 
                    $prestudent->person_id = $person_id; 
                    $prestudent->anmerkung = trim($row_applicant->remarks).' '.trim($row_applicant->notesacademic);
                    $prestudent->rt_punkte1 = trim($row_applicant->interviewscorepf); 
                    $prestudent->rt_punkte2 = trim($row_applicant->interviewscorecs);
                    $prestudent->rt_punkte3 = trim($row_applicant->interviewscorelc); 
                    $prestudent->punkte = trim($row_applicant->interviewscoretotal); 
                    $prestudent->zgvort = trim($row_applicant->accesscountry); 
                    $prestudent->zgvdatum = $datum->formatDatum(trim($row_applicant->accessdate),'Y-m-d'); 
                    $prestudent->zgv_code = trim($row_applicant->accesscode); 
                    $prestudent->new = false; 
                    $prestudent->anmeldungreihungstest = $datum->formatDatum(trim($row_applicant->interviewdate),'Y-m-d'); 
                    $prestudent->reihungstestangetreten = ($row_applicant->interviewtaken_boolean == '1')?true:false; 
                    $prestudent->aufmerksamdurch_kurzbz='k.A.';
                    $prestudent->studiengang_kz= '0'; 
                    if($row_applicant->chosen_studyprogram_year != '')
                    {
                        $studiengang_kz = strtolower(mb_substr($row_applicant->chosen_studyprogram_year, 0,-1)); 
                        $qry_stg = "SELECT studiengang_kz FROM public.tbl_studiengang WHERE kurzbz=".$db->db_add_param($studiengang_kz).';';
                        if($result_stg = $db->db_query($qry_stg))
                        {
                            if($row_stg = $db->db_fetch_object($result_stg))
                            {
                                $prestudent->studiengang_kz=$row_stg->studiengang_kz;
                            }
                        }
                    }
					
					if($prestudent->save())
					{
						$statistik_prestudent_update++;
					}
					else
					{
						$statistik_prestudent_update_fehler++;
                        logMessage('Fehler beim Update des Prestudenten '.$prestudent->errormsg); 
					}
					
					$statistik_gefundene_prestudenten++;

                }
                else
                {
                    // prestudent neu anlegen
                    $prestudent = new prestudent();
                    $prestudent->ext_id = trim($row_applicant->zk_p_applicantid_t); 
                    $prestudent->person_id = $person_id; 
                    $prestudent->anmerkung = trim($row_applicant->remarks).' '.trim($row_applicant->notesacademic);
                    $prestudent->rt_punkte1 = trim($row_applicant->interviewscorepf); 
                    $prestudent->rt_punkte2 = trim($row_applicant->interviewscorecs);
                    $prestudent->rt_punkte3 = trim($row_applicant->interviewscorelc); 
                    $prestudent->punkte = trim($row_applicant->interviewscoretotal); 
                    $prestudent->zgvort = trim($row_applicant->accesscountry); 
                    $prestudent->zgvdatum = $datum->formatDatum(trim($row_applicant->accessdate),'Y-m-d'); 
                    $prestudent->zgv_code = trim($row_applicant->accesscode); 
                    $prestudent->new = true; 
                    $prestudent->anmeldungreihungstest = $datum->formatDatum(trim($row_applicant->interviewdate),'Y-m-d'); 
                    $prestudent->reihungstestangetreten = ($row_applicant->interviewtaken_boolean == '1')?true:false; 
                    $prestudent->aufmerksamdurch_kurzbz='k.A.';
                    $prestudent->studiengang_kz= '0'; 
                    if($row_applicant->chosen_studyprogram_year != '')
                    {
                        $studiengang_kz = strtolower(mb_substr($row_applicant->chosen_studyprogram_year, 0,-1)); 
                        $qry_stg = "SELECT studiengang_kz FROM public.tbl_studiengang WHERE kurzbz=".$db->db_add_param($studiengang_kz).';';
                        if($result_stg = $db->db_query($qry_stg))
                        {
                            if($row_stg = $db->db_fetch_object($result_stg))
                            {
                                $prestudent->studiengang_kz=$row_stg->studiengang_kz;
                            }
                        }
                    }
                    
                    if($prestudent->save())
                    {            
                        $prestudent_id = $prestudent->prestudent_id; 
                        $qry =  "UPDATE sync.lbs_sync_applicant_person SET prestudent_id =".$db->db_add_param($prestudent->prestudent_id)."
                            WHERE zk_p_applicantid_t =".$db->db_add_param($row_applicant->zk_p_applicantid_t).";";
                        if(!$db->db_query($qry))
                            logMessage("Fehler beim Schreiben des Sync Eintrages:".$qry);
                        
                        $statistik_prestudent++; 
                    }
                    else 
                    {
                        logMessage("Fehler beim Anlegen des Prestudenten:".$prestudent->errormsg);
                        $statistik_prestudent_fehler++;
                    }
                }
                
                if($prestudent_id != '')
                {
					//Dokument "Approval" hinzufügen, falls vorhanden
                    if(stristr($row_applicant->docapprovalstatus, 'sent') !== false)
					{
						$qry_approval = "SELECT * FROM public.tbl_dokumentprestudent WHERE prestudent_id = ".$db->db_add_param($prestudent_id, FHC_INTEGER)."
							AND dokument_kurzbz='Approval'";
						if($result_approval = $db->db_query($qry_approval))
						{
							if($row_approval=$db->db_fetch_object($result_approval))
							{
                            }
                            else
                            {
								//Dokument noch nicht in Datenbank -> erstellen
								$dokument=new dokument();
								$dokument->dokument_kurzbz='Approval';
								$dokument->prestudent_id=$prestudent_id;
								$dokument->mitarbeiter_uid='sekretariat';
                                $dokument->new = true; 
								$dokument->datum = $datum->formatDatum(trim($row_applicant->docapprovalsenddate),'Y-m-d');
								$dokument->updateamum = date('Y-m-d H:i:s');
								$dokument->updatevon = 'Sync';
								$dokument->insertamum = date('Y-m-d H:i:s');
								$dokument->insertvon = 'Sync';
							
								if($dokument->save())
								{
									$statistik_dokumente_neu++;
								}
								else
								{
									$statistik_dokumente_fehler++;
                                    logMessage('Fehler beim Anlegen des Dokuments aufgetreten '.$dokument->errormsg); 
								}
							}
						}
					}
					
					//Dokument "Contract" hinzufügen, falls vorhanden
                    if(stristr($row_applicant->doccontractstatus, 'sent') !== false)
					{
						$qry_contract = "SELECT * FROM public.tbl_dokumentprestudent WHERE prestudent_id = ".$db->db_add_param($prestudent_id, FHC_INTEGER)."
							AND dokument_kurzbz='Contract'";
						if($result_contract = $db->db_query($qry_contract));
						{
							if($row_contract=$db->db_fetch_object($result_contract))
							{
                            }
                            else
                            {
								//Dokument noch nicht in Datenbank -> erstellen
								$dokument=new dokument();
								$dokument->dokument_kurzbz='Contract';
								$dokument->prestudent_id=$prestudent_id;
								$dokument->mitarbeiter_uid='sekretariat';
                                $dokument->new = true; 
                                $dokument->datum=$datum->formatDatum(trim($row_applicant->doccontractsenddate),'Y-m-d');
								$dokument->updateamum = date('Y-m-d H:i:s');
								$dokument->updatevon = 'Sync';
								$dokument->insertamum = date('Y-m-d H:i:s');
								$dokument->insertvon = 'Sync';
							
								if($dokument->save())
								{
									$statistik_dokumente_neu++;
								}
								else
								{
									$statistik_dokumente_fehler++;
                                    logMessage('Fehler beim Anlegen des Dokuments aufgetreten '.$dokument->errormsg); 
								}
							}
						}
					}

					//Dokument "scholarshipJHFletter" hinzufügen, falls vorhanden
                    if(stristr($row_applicant->scholarshipjhfletterstatus, 'sent') !== false)
					{
						$qry_scholars = "SELECT * FROM public.tbl_dokumentprestudent WHERE prestudent_id = ".$db->db_add_param($prestudent_id, FHC_INTEGER)."
							AND dokument_kurzbz='Scholars'";
						if($result_scholars = $db->db_query($qry_scholars))
						{
							if($row_scholars=$db->db_fetch_object($result_scholars))
							{
                            }
                            else
                            {
								//Dokument noch nicht in Datenbank -> erstellen
								$dokument=new dokument();
								$dokument->dokument_kurzbz='Scholars';
								$dokument->prestudent_id=$prestudent_id;
								$dokument->mitarbeiter_uid='sekretariat';
                                $dokument->new = true; 
								$dokument->datum=$datum->formatDatum(trim($row_applicant->scholarshipjhflettersenddate),'Y-m-d');
								$dokument->updateamum = date('Y-m-d H:i:s');
								$dokument->updatevon = 'Sync';
								$dokument->insertamum = date('Y-m-d H:i:s');
								$dokument->insertvon = 'Sync';

								if($dokument->save())
								{
									$statistik_dokumente_neu++;
								}
								else
								{
									$statistik_dokumente_fehler++;
                                    logMessage('Fehler beim Anlegen des Dokuments aufgetreten '.$dokument->errormsg); 
								}
							}
						}
					}
					
                    // wenn vorhanden, abgewiesen status erstellen
                    //if(stristr($row_applicant->status, 'rejected') === FALSE)
                    if(strpos($row_applicant->status, 'rejected')!== false)
                    {
                        $qry_abgewiesen = "SELECT * FROM public.tbl_prestudentstatus where prestudent_id =".$db->db_add_param($prestudent_id, FHC_INTEGER)." 
                            AND status_kurzbz = 'Abgewiesener';";
                        if($result_abgewiesen = $db->db_query($qry_abgewiesen))
                        {
                            if($row_abgewiesen= $db->db_fetch_object($result_abgewiesen))
                            {
                                // gibt Status schon
                            }
                            else
                            {
                                $prestudent = new prestudent(); 
                                $prestudent->load($prestudent_id); 
                                $prestudent->status_kurzbz = 'Abgewiesener'; 
                                $prestudent->ausbildungssemester = '0';
                                $prestudent->studiensemester_kurzbz ='WS2014'; 
                                $prestudent->new = true; 
                                if($prestudent->save_rolle())
                                {
                                    $statistik_status_insert++;
                                }
                                else
                                {
                                    $statistik_status_error++;
                                    logMessage("Fehler beim Anlegen des Prestudentenstatus - ".$prestudent->errormsg);
                                }
                            }
                        }
                    }
                    else 
                    {
                        // nicht gefunden
                    }

                    // wenn vorhanden, interessent status vorhanden
                    if(stristr($row_applicant->status, 'notApproved') === false)
                    {
                        // nicht gefunden, stristr gibt sonst string zurück
                    }
                    else
                    {
                        $qry_interessent = "SELECT * FROM public.tbl_prestudentstatus where prestudent_id =".$db->db_add_param($prestudent_id, FHC_INTEGER)." 
                            AND status_kurzbz = 'Interessent';";
                        if($result_interessent = $db->db_query($qry_interessent))
                        {
                            if($row_interessent= $db->db_fetch_object($result_interessent))
                            {
                                // gibt Status schon
                            }
                            else
                            {
                                $prestudent = new prestudent(); 
                                $prestudent->load($prestudent_id); 
                                $prestudent->status_kurzbz = 'Interessent'; 
                                $prestudent->ausbildungssemester = '0';
                                $prestudent->studiensemester_kurzbz ='WS2014'; 
                                $prestudent->new = true; 
                                if($prestudent->save_rolle())
                                {
                                    $statistik_status_insert++;
                                }
                                else
                                {
                                    $statistik_status_error++;
                                    logMessage("Fehler beim Anlegen des Prestudentenstatus - ".$prestudent->errormsg);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

    logMessage("----- Bewerberdatenmigration -------");
    logMessage("Anlegen neuer Personen: ".$statistik_neue_personen);
    logMessage("gefundene Personen: ".$statistik_gefundene_personen);
    logMessage("Anlegen neuer Kontobewegungen: ".$statistik_konto);
    logMessage("Fehler bei Anlegen von Konto: ".$statistik_konto_fehler);
    logMessage("Neue Prestudenten hinzugefügt: ".$statistik_prestudent);
    logMessage("Fehler bei Prestudent hinzufügen: ".$statistik_prestudent_fehler);    
    logMessage("Neue Dokumente: ".$statistik_dokumente_neu);
    logMessage("Fehler bei Dokumenten: ".$statistik_dokumente_fehler);
    logMessage("Neue Adressen: ".$statistik_adresse_neu);
    logMessage("Fehler bei Adressen: ".$statistik_adresse_fehler);   



/**
 * Synchronisiert die Kontaktdaten
 * @param $person_id ID der Person im FHComplete
 * @param $kontakttyp email|fax|telefon|...
 * @param $kontakt telefonnummer, emailadresse, etc
 */
function sync_kontakt($person_id, $kontakttyp, $kontakt)
{
	global $db, $statistik_kontakt_neu;
	global $statistik_kontakt_aktualisiert,$statistik_kontakt_fehler;
	
	if($kontakt!='')
	{
		$qry = "SELECT * FROM public.tbl_kontakt WHERE kontakttyp=".$db->db_add_param($kontakttyp)." AND person_id=".$db->db_add_param($person_id);

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
				//Noch nicht vorhanden
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





function logMessage($message)
{
	global $loghandle;

	$time = date('Y-m-d H:i:s');
	echo "<br>".$message;
	fputs($loghandle, "\n".$time.' >> '.$message);
}

?>
