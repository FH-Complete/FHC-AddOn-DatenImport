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
 * Importiert Bilder aus dem Ordner data/pics in die Tabelle sync.tbl_di_bilder und koennen
 * von dort per Mapping in Tabelle Person und akte verteilt werden
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
	<title>Bildimport</title>
</head>
<body>
<h1>Bild Import</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/datenimport'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$db = new basis_db();
$data_path='../data/pics';

$qry = 'SELECT 1 FROM sync.tbl_di_bilder Limit 1';
if(!@$db->db_query($qry))
{
	$qry = "
	CREATE SEQUENCE sync.seq_di_bilder_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;
		 
	CREATE TABLE sync.tbl_di_bilder(
		id bigint,
		status character(1),
		lastupdate timestamp,
		ext_id varchar(32),
		bild_gross text,
		bild_klein text
	);
		
	ALTER TABLE sync.tbl_di_bilder ALTER COLUMN id SET DEFAULT nextval('sync.seq_di_bilder_id');
	";
	if(!$db->db_query($qry))
			die('Fehler beim Anlegen der Tabellen');
}

if ($handle = opendir($data_path)) 
{
    while (false !== ($entry = readdir($handle))) 
    {
        if ($entry != "." && $entry != ".." && $entry != ".svn") 
        {
        	$ext_id = mb_substr($entry,0,mb_strrpos($entry,'.'));
        	
        	$entry = $data_path.'/'.$entry;
            echo "<br>$entry\n";            
            
            //$ext_id = basename($entry,'.jpg');
            
            //groesse auf maximal 827x1063 begrenzen
            $file_big = resize($entry, 827, 1063);
            //auslesen
            $fp = fopen($file_big,'r');
            $content = fread($fp, filesize($file_big));
            fclose($fp);
            
            $bildcontent_gross = base64_encode($content);
            unlink($file_big);
            
            //groesse auf maximal 101x130 begrenzen
            $file_small=resize($entry, 101, 130);

            //File oeffnen
            $fp = fopen($file_small,'r');
            //auslesen
            $content = fread($fp, filesize($file_small));
            fclose($fp);
            //in base64 umrechnen
            $bildcontent_klein = base64_encode($content);
            unlink($file_small);
            
            $qry = "INSERT INTO sync.tbl_di_bilder(status, lastupdate, ext_id, bild_gross, bild_klein) VALUES('i',now(),".
              	$db->db_add_param($ext_id).','.
              	$db->db_add_param($bildcontent_gross).','.
              	$db->db_add_param($bildcontent_klein).');';
            
            if(!$db->db_query($qry))
            	echo 'Error';
            else
            	echo '+';              
        }
    }
    closedir($handle);
}

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
		
	$tempfname = tempnam('/tmp','fhcimg');
	imagejpeg($image_p, $tempfname, 80);
		
	imagedestroy($image_p);
	@imagedestroy($image);
	return $tempfname;
}

?>
