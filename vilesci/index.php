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
<html style="height:100%">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
	<title>FHC AddOn Datenimport</title>
</head>
<body style="height:100%">

<h1>AddOn Datenimport</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/datenimport'))
{
	die('Sie haben keine Berechtigung fuer dieses AddOn');
}
?>
<button onclick="document.getElementById('di_main').src='diq_frameset.html'" title="Datenquellen bearbeiten"><img src="database-postgres.svg" height="40"/></button>
<button onclick="document.getElementById('di_main').src='dii_frameset.html'" title="Daten importieren"><img src="document-save.svg" height="40" /></button>
<button onclick="document.getElementById('di_main').src='dim_frameset.html'" title="Datenmapping bearbeiten"><img src="DataSources.svg" height="40"/></button>
<button onclick="document.getElementById('di_main').src='dis_frameset.html'" title="Daten synchronisieren"><img src="view-refresh.svg" height="40" /></button>

<iframe id="di_main" src="" width="100%" style="height:90%">
</iframe>
