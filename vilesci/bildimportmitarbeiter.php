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
require_once('../../../include/akte.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/fotostatus.class.php');

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
<h1>LBS Datenmigration - Bild Import</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/lbsdatenmigration'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$db = new basis_db();
$data_path='data/pics_lkt';
$logfile='bildimportmitarbeiter.log';

// Logfile oeffnen
if(!$loghandle=fopen($logfile, 'w'))
	die("Kann Logfile $logfile nicht öffnen!");

$anzahl_bilder=0;
$anzahl_uebersprungen=0;
$anzahl_eingefuegt=0;

if ($dirhandle = opendir($data_path)) 
{
    while (false !== ($file = readdir($dirhandle))) 
	{
		if($file!='.' && $file!='..' && mb_substr($file, -3)=='jpg')
		{
			$anzahl_bilder++;

			logMessage("Lese Datei $file");

			$lecturer_id = mb_substr($file, 0, -4);

			$qry = "SELECT 
						person_id 
					FROM 
						sync.lbs_sync_lecturer_person
					WHERE zk_p_lecturerid_t=".$db->db_add_param($lecturer_id);

			if($result_person = $db->db_query($qry))
			{
				if($row_person = $db->db_fetch_object($result_person))
				{
					$person_id=$row_person->person_id;
				}
				else
				{
					logMessage("Ueberspringe Bild $file -> keine passende Person gefunden");
					$anzahl_uebersprungen++;
					continue;
				}
			}
		
			$filename = $data_path.'/'.$file;
			
			//groesse auf maximal 827x1063 begrenzen
			resize($filename, 827, 1063);
			
			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			
			$akte = new akte();
			
			if($akte->getAkten($person_id, 'Lichtbil'))
			{
				if(numberOfElements($akte->result)>0)
				{
					$akte = $akte->result[0];
					$akte->new = false;
				}
				else 
					$akte->new = true;
			}
			else 
			{
				$akte->new = true;
			}
		
			$akte->dokument_kurzbz = 'Lichtbil';
			$akte->person_id = $person_id;
			$akte->inhalt = base64_encode($content);
			$akte->mimetype = "image/jpg";
			$akte->erstelltam = date('Y-m-d H:i:s');
			$akte->gedruckt = false;
			$akte->titel = "Lichtbild_".$person_id.".jpg";
			$akte->bezeichnung = "Lichtbild gross";
			$akte->updateamum = date('Y-m-d H:i:s');
			$akte->updatevon = 'import';
			$akte->insertamum = date('Y-m-d H:i:s');
			$akte->insertvon = 'import';
			$akte->uid = '';
		
			if(!$akte->save())
			{
				logMessage('Fehler: '.$akte->errormsg);
			}
		
			//groesse auf maximal 101x130 begrenzen
			resize($filename, 101, 130);
		
			//in DB speichern           
			//File oeffnen
			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			//in base64 umrechnen
			$content = base64_encode($content);
				$person = new person();
			if($person->load($person_id))
			{
				//base64 Wert in die Datenbank speichern
				$person->foto = $content;
				$person->new = false;				
				if($person->save())
				{
					$fs = new fotostatus();
					$fs->person_id=$person->person_id;
					$fs->fotostatus_kurzbz='hochgeladen';
					$fs->datum = date('Y-m-d');
					$fs->insertamum = date('Y-m-d H:i:s');
					$fs->insertvon = 'import';
					$fs->updateamum = date('Y-m-d H:i:s');
					$fs->updatevon = 'import';
					if(!$fs->save(true))
						echo logMessage('Fehler beim Setzen des Bildstatus');
					else
					{
						$anzahl_eingefuegt++;
						logMessage('Bild wurde erfolgreich gespeichert');
					}
				}
				else
					echo logMessage($person->errormsg);
			}
			else
				logMessage($person->errormsg);
			flush();
			ob_flush();
		}
    }

    closedir($dirhandle);
}
logMessage("Gesamt: ".$anzahl_bilder);
logMessage("Eingefuegt: ".$anzahl_eingefuegt);
logMessage("Uebersprungen:" .$anzahl_uebersprungen);

fclose($loghandle);


function logMessage($message)
{
	global $loghandle;

	echo '<br>'.$message;
	$time = date('Y-m-d H:i:s');
	fputs($loghandle, "\n".$time.' >> '.$message);
}

function resize($filename, $width, $height)
{
	$ext='jpg';

	// Hoehe und Breite neu berechnen
	list($width_orig, $height_orig) = getimagesize($filename);

	if ($width && ($width_orig < $height_orig)) 
	{
	   $width = ($height / $height_orig) * $width_orig;
	}
	else
	{
	   $height = ($width / $width_orig) * $height_orig;
	}
	
	$image_p = imagecreatetruecolor($width, $height);                       
			
	$image = imagecreatefromjpeg($filename);
	
	//Bild nur verkleinern aber nicht vergroessern
	if($width_orig>$width || $height_orig>$height)
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	else 
		$image_p = $image;
		
	imagejpeg($image_p, $filename, 80);
		
	imagedestroy($image_p);
	@imagedestroy($image);
}

?>
