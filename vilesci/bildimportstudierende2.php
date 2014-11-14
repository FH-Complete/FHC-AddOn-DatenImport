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

ini_set('max_execution_time', 3000);

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
$data_path='data/pics';

$anzahl_bilder=0;
$anzahl_uebersprungen=0;
$anzahl_eingefuegt=0;

$qry = "SELECT 
	zk_p_studentid_t, person_id, uid
FROM 
	public.tbl_benutzer 
	JOIN public.tbl_person USING(person_id)
	JOIN sync.lbs_sync_student_uid USING(uid)
WHERE
	tbl_person.foto is null";

if ($result = $db->db_query($qry)) 
{
    while ($row = $db->db_fetch_object($result)) 
	{
		$filename = $data_path.'/'.$row->zk_p_studentid_t.'.jpg';
		if(file_exists($filename))
		{
			//groesse auf maximal 827x1063 begrenzen
			resize($filename, 827, 1063);
			
			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			
			$akte = new akte();
			
			if($akte->getAkten($row->person_id, 'Lichtbil'))
			{
				if(count($akte->result)>0)
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
			$akte->person_id = $row->person_id;
			$akte->inhalt = base64_encode($content);
			$akte->mimetype = "image/jpg";
			$akte->erstelltam = date('Y-m-d H:i:s');
			$akte->gedruckt = false;
			$akte->titel = "Lichtbild_".$row->person_id.".jpg";
			$akte->bezeichnung = "Lichtbild gross";
			$akte->updateamum = date('Y-m-d H:i:s');
			$akte->updatevon = 'import';
			$akte->insertamum = date('Y-m-d H:i:s');
			$akte->insertvon = 'import';
			$akte->uid = '';
		
			if(!$akte->save())
			{
				echo "Fehler beim Speichern der Akte";
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
			if($person->load($row->person_id))
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
						echo 'Fehler beim Setzen des Bildstatus';
					else
					{
						$anzahl_eingefuegt++;
						echo 'Bild wurde erfolgreich gespeichert';
					}
				}
				else
					echo $person->errormsg;
			}
			else
				echo $person->errormsg;
			flush();
			ob_flush();
		}
    }
}
echo "Gesamt: ".$anzahl_bilder;
echo "Eingefuegt: ".$anzahl_eingefuegt;
echo "Uebersprungen:" .$anzahl_uebersprungen;

function resize($filename, $width, $height)
{
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
