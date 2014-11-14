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
	<title>LBS Datenmigration - Bildfix</title>
</head>
<body>
<h1>LBS Datenmigration - Bild korrektur</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/lbsdatenmigration'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$db = new basis_db();

$qry_personen = "SELECT person_id, foto FROM public.tbl_person WHERE foto is not null";

if($result = $db->db_query($qry_personen))
{
	while($row = $db->db_fetch_object($result))
	{
		$filename = '/tmp/image';

		$foto = base64_decode($row->foto);
		if(file_put_contents($filename, $foto))
		{	
			//groesse auf maximal 101x130 begrenzen
			resize($filename, 101, 130);

			if($content = file_get_contents($filename))
			{
				//in base64 umrechnen
				$content = base64_encode($content);
				$qry_upd = "UPDATE public.tbl_person set foto=".$db->db_add_param($content)." WHERE person_id=".$db->db_add_param($row->person_id);
				if($db->db_query($qry_upd))
					echo "<br>Foto für $row->person_id gefixt";
				else
					echo "<br>Fehler biem Speichern des Bildes";
			}
			else
			{
				echo "<br>File Get Contents Failed für $row->person_id";
			}
		}
		else
			echo "<br>File Put Contents failed für $row->person_id";
	}
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
