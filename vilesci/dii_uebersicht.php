<?php
/* Copyright (C) 2013 fhcomplete.org
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('diq.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$nl="\n";

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('addon/datenimport'))
	die('Sie haben keine Rechte fuer dieses AddOn! Schleich Dich!');

if($rechte->isBerechtigt('addon/datenimport', 'suid'))
	$write_admin=true;

// Daten holen
$diq = new diq();
if (!$diq->loadAll())
    die($diq->errormsg);

//$htmlstr = "<table class='liste sortable'>\n";
$htmlstr = "<form name='formular'><input type='hidden' name='check' value=''></form><table class='tablesorter' id='t1'>\n";
$htmlstr .= "   <thead><tr>\n";
$htmlstr .= "    <th onmouseup='document.formular.check.value=0'>ID</th>
		<th>TableName</th>
		<th>&sum;</th>
		<th>CSV URI</th>
		<th>CSV Tab</th>
		<th>ODS URI</th>
		<th>DB Typ</th>
		<th>Action</th>
		";
$htmlstr .= "   </tr></thead><tbody>\n";
$i = 0;
foreach ($diq->result as $diquelle)
{
    //$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
	$htmlstr .= "   <tr>\n";
	$htmlstr .= "       <td><a href='diq_details.php?diq_id=".$diquelle->diq_id."' target='dii_import'>".$diquelle->diq_id."</a></td>".$nl;
	$htmlstr .= "       <td><a href='diq_details.php?diq_id=".$diquelle->diq_id."' target='dii_import'>".$diquelle->diq_tablename."</a></td>".$nl;
	$htmlstr .= "       <td>".$diquelle->diq_num_rows."</td>".$nl;
	$htmlstr .= "       <td>".$diquelle->csv_uri."</td>".$nl;
	$htmlstr .= "       <td>".$diquelle->csv_tab."</td>".$nl;
	$htmlstr .= "       <td>".$diquelle->ods_uri."</td>".$nl;
	$htmlstr .= "       <td>".$diquelle->db_typ."</td>".$nl;
	$htmlstr .= '       <td><a href="dii_import.php?diq_id='.$diquelle->diq_id.'" target="dii_import">
								<img title="'.$diquelle->diq_tablename.' importieren" src="document-save.png" />
							</a>
							<img title="'.$diquelle->diq_tablename.' importieren" src="edit-clear.png" />
							<img title="'.$diquelle->diq_tablename.' importieren" src="edit-delete.png" />
						</td>'.$nl;
	$htmlstr .= "   </tr>".$nl;
	$i++;
}
$htmlstr .= "</tbody></table>".$nl;


?>
<html>
<head>
<title>Datenimport</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
<script type="text/javascript" src="../../../include/js/jquery.js"></script>
<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
<style>
table.tablesorter tbody td
{
	margin: 0;
	padding: 0;
	vertical-align: middle;
}
</style>
<script language="JavaScript" type="text/javascript">
$(document).ready(function() 
		{ 
			$("#t1").tablesorter(
			{
				widgets: ["zebra"]
			}); 
		});
</script>

</head>

<body class="background_main">
<?php 
    echo $htmlstr;
?>
</body>
</html>
