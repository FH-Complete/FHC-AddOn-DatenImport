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
 * FH-Complete Addon Template Datenbank Check
 *
 * Prueft und aktualisiert die Datenbank
 */
require_once('../../config/system.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
$db = new basis_db();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<title>Addon Datenbank Check</title>
</head>
<body>
<h1>Addon Datenbank Check</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/addon'))
{
    exit('Sie haben keine Berechtigung für die Verwaltung von Addons');
}

echo '<h2>Aktualisierung der Datenbank</h2>';

// Code fuer die Datenbankanpassungen

//Neue Berechtigung für das Addon hinzufügen
if($result = $db->db_query("SELECT * FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='addon/datenimport'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) 
				VALUES('addon/datenimport','AddOn Datenimport');";

		if(!$db->db_query($qry))
			echo '<strong>Berechtigung: '.$db->db_last_error().'</strong><br>';
		else 
			echo 'Neue Berechtigung addon/datenimport hinzugefuegt!<br>';
	}
}

// Datenimport (di) Quellen
if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_di_quelle"))
{

	$qry = 'CREATE TABLE addon.tbl_di_quelle
			(
				diq_id serial,
				diq_tablename varchar(32),
				diq_keyattribute varchar(512),
				diq_viewname varchar(32),
				diq_view text,
				diq_order integer,
				diq_limit integer,
				csv_uri text,
				csv_tab varchar(8),
				ods_uri text,
				db_typ varchar(32),
				db_host varchar(256),
				db_port integer,
				db_name varchar(64),
				db_user varchar(64),
				db_passwd varchar(64),
				sql text,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32),
				CONSTRAINT pk_diq PRIMARY KEY (diq_id)
			);
			GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_di_quelle TO vilesci;';

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_di_quelle: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' addon.tbl_di_quelle: Tabelle addon.tbl_di_quelle hinzugefuegt!<br>';

}

// Datenimport (di) Mapping
if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_di_mapping"))
{

	$qry = 'CREATE TABLE addon.tbl_di_mapping
			(
				dim_id serial,
				diq_id bigint,
				diq_attribute varchar(256),
				fhc_attribute varchar(256),
				fhc_datatype varchar(32),
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32),
				CONSTRAINT pk_dim PRIMARY KEY (dim_id)
			);
			GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_di_mapping TO vilesci;
			ALTER TABLE addon.tbl_di_mapping ADD CONSTRAINT fk_diq_id FOREIGN KEY (diq_id) 
				REFERENCES addon.tbl_di_quelle(diq_id)  
				ON UPDATE CASCADE ON DELETE RESTRICT';

	if(!$db->db_query($qry))
		echo '<strong>addon.tbl_di_mapping: '.$db->db_last_error().'</strong><br>';
	else 
		echo ' addon.tbl_di_mapping: Tabelle addon.tbl_di_mapping hinzugefuegt!<br>';

}

echo '<br>Aktualisierung abgeschlossen<br><br>';
echo '<h2>Gegenprüfung</h2>';

// Liste der verwendeten Tabellen / Spalten des Addons
$tabellen=array(
	"addon.tbl_di_quelle"  => array("diq_id", "diq_tablename", "diq_keyattribute", "diq_viewname", "diq_view", "diq_order", "diq_limit", "csv_uri", "csv_tab", "ods_uri", "db_typ","db_host","db_port","db_name","db_user","db_passwd","sql","insertamum","insertvon","updateamum","updatevon")
	,"addon.tbl_di_mapping" => array("dim_id","diq_id","diq_attribute","fhc_attribute","fhc_datatype","insertamum","insertvon","updateamum","updatevon")
);


$tabs=array_keys($tabellen);
$i=0;
foreach ($tabellen AS $attribute)
{
	$sql_attr='';
	foreach($attribute AS $attr)
		$sql_attr.=$attr.',';
	$sql_attr=substr($sql_attr, 0, -1);

	if (!@$db->db_query('SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
		echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
	else
		echo '- '.$tabs[$i].': OK<br>';
	flush();
	$i++;
}
?>
