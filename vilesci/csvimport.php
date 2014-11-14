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

ini_set('max_execution_time', 3000);

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/lbsdatenmigration'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$db = new basis_db();
$data_path='data/csv';
$logfile='csvimport.log';

// Logfile oeffnen
if(!$loghandle=fopen($logfile, 'w'))
	die("Kann Logfile $logfile nicht öffnen!");

$inserts='';
if ($dirhandle = opendir($data_path)) 
{
    while (false !== ($file = readdir($dirhandle))) 
	{
		if($file!='.' && $file!='..' && mb_substr($file, -3)=='csv')
		{
			$anzahl_rows=0;
			$anzahl_inserts=0;
			$anzahl_empty=0;

			$filehandle = fopen ($data_path.'/'.$file,"r");
			logMessage("Lese Datei $file");

			// CSV Name = Tabellenname
			$tablename = 'lbs_'.mb_substr($file,0, -4);

			// Alle Einträge aus der Tabelle löschen
			$qry = "DELETE FROM sync.$tablename";
			$db->db_query($qry);
	
			$item_counter=0;

			// Jede Zeile der CSV Datei durchlaufen und Insert zusammenbauen
			while ( ($csvdata = fgetcsv ($filehandle)) !== FALSE ) 
			{
				$item_counter++;
				$anzahl_rows++;

				// Zeilen im CSV die keine Daten enthalten werden vorab herausgefiltert
				$empty=true;
				foreach($csvdata as $row)
				{
					if($row!='')
						$empty=false;
				}
				if($empty)
				{
					//logMessage("Überspringe leeren Datensatz $item_counter");
					$anzahl_empty++;
					continue;
				}

				// Neuen Eintrag erstellen
				$qry = "INSERT INTO sync.".$tablename." VALUES(";
				foreach($csvdata as $item)	
				{
					$qry.=$db->db_add_param(clean($item)).',';
				}

				$anzahl_inserts++;
				$inserts.= mb_substr($qry,0,-1).");\n";

			}

			fclose ($filehandle);
			logMessage('Anzahl Datensätze:'.$anzahl_rows);
			if($anzahl_empty>0)
				logMessage('Davon leere Einträge: '.$anzahl_empty);

			if($result = $db->db_query($inserts))
			{
				logMessage("Eingefügte Datensätze: ".$anzahl_inserts);
				$inserts='';
			}
			else
			{
				logMessage("Fehler beim Einfügen:".$db->db_last_error());
				logMessage("Query: ".$inserts);
			}
			flush();
			ob_flush();
		}
    }

	logMessage("Import abgeschlossen");
    closedir($dirhandle);
}

fclose($loghandle);

function logMessage($message)
{
	global $loghandle;

	$time = date('Y-m-d H:i:s');
	echo "<br>".$message;
	fputs($loghandle, "\n".$time.' >> '.$message);
}

// Entfernt unsichtbare Steuerzeichen und Sonderzeichen die in den CSVs drinnen sind
function clean($item)
{
	// Vertical Tab
	$item = mb_str_replace('','',$item);

	// Group Separator eventuell beim Parsen notwendig
	//$item = mb_str_replace('','',$item);

	$item = trim($item);
	return $item;
}
?>
