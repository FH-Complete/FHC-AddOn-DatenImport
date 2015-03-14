<?php
/* Copyright (C) 2006 Technikum-Wien
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
 ?>
 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>SyncDetails</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">

</head>
<body style="background-color:#eeeeee;">
<?php
	require_once('../../../config/vilesci.config.inc.php');
	require_once('../../../include/globals.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/benutzerberechtigung.class.php');
	require_once('diq.class.php');
	require_once('dim.class.php');
	$nl="\n";
	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
	$user = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('addon/datenimport'))
		die('Sie haben keine Berechtigung fuer dieses AddOn!');
	
	$reloadstr = '';  // neuladen der liste im oberen frame
	$htmlstr = '';
	$errorstr = ''; //fehler beim insert
	$sel = '';
	$chk = '';

	
	
	if(!$rechte->isBerechtigt('addon/datenimport', null, 's'))
		die('Sie haben keine Berechtigung fuer diese Aktion');
	if(!isset($_REQUEST['diq_id']))
		die ('diq_id is not set!');
	$diq = new diq();
	$diq->load((int)$_REQUEST["diq_id"]);
	$dim = new dim();
	$dim->loadDIQ((int)$_REQUEST["diq_id"]);
	if(isset($_REQUEST['action']))
	{
		if ($_REQUEST['action']=='changestatus')
		{
			echo 'Change Status';
			if(!isset($_REQUEST['newstatus']))
				die ('newstatus is not set!');
			$qry='UPDATE sync.'.$diq->diq_tablename." SET status='".$_REQUEST['newstatus']."' WHERE status='".$_REQUEST['status']."';";
			$diq->db_query($qry);
			echo '<div align="right">Status changed!</div>';
		}
		if ($_REQUEST['action']=='delete')
		{
			echo 'Clear deleted datasets';
			//$fhc_table=$dim->getFhcTable();
			//DELETE FROM public.tbl_student WHERE ext_id IN (SELECT id FROM sync.tbl_di_student WHERE status='d')
			$qry='DELETE FROM '.$dim->getFhcTable().' WHERE ext_id IS NOT NULL AND ext_id IN (SELECT id FROM sync.'.$diq->diq_tablename." WHERE status='d');";
			if ($diq->db_query($qry))
			{
				echo '<div align="right">Destination Datasets deleted!</div>';
				$qry='DELETE FROM sync.'.$diq->diq_tablename." WHERE status='d';";
				if ($diq->db_query($qry))
					echo '<div align="right">Source Datasets deleted!</div>';
			}
		}
		if ($_REQUEST['action']=='clean')
		{
			echo 'Clean status (set status=d where not synced)';
			//$fhc_table=$dim->getFhcTable();
			$qry='UPDATE sync.'.$diq->diq_tablename." SET status='i' WHERE status ='u' AND id NOT IN (SELECT ext_id FROM ".$dim->getFhcTable()." WHERE ext_id IS NOT NULL);";
				if ($diq->db_query($qry))
					echo '<div align="right">Status changed from u to i!</div>';
			
		}
	}
	if(!isset($_REQUEST['status']))
		die ('status is not set!');
	echo "<div class='kopf'>Data Synchro - DIQ-ID: ".$_REQUEST['diq_id']."</div>\n";
	echo '<div align="right"><form method="POST" action="dis_details.php?diq_id='.$_REQUEST['diq_id'].'&status='.$_REQUEST['status'].'&action=changestatus">'."\n";
	echo 'Change Status from "'.$_REQUEST['status'].'" to <input type="text" name="newstatus" size="1" maxlength="1" value="u"> ';
	echo ' <input type="submit" value="Change">'."\n";
	echo '</form></div>'."\n";
	echo '<br /><br />'."\n"; 
	
	$diq_table='sync.'.$diq->diq_tablename;
	// Datensaetze zum Status holen
	$qry="SELECT * FROM $diq_table WHERE status='".$_REQUEST['status']."'";
	if (!is_null($diq->diq_limit) && $diq->diq_limit<1000)
		$qry.=" LIMIT $diq->diq_limit;";
	else
		$qry.=" LIMIT 100;";
		
	if(!$dim->loadData($qry))
		die($qry);
		
	$htmlstr = "<form name='formular'><input type='hidden' name='check' value=''></form><table class='tablesorter' id='t1'>\n";
	$htmlstr .= "   <thead><tr>\n";
	$htmlstr .= "    <th onmouseup='document.formular.check.value=0'>ID</th>
			<th>Status</th>
			<th>LastUpdate</th>
			";
	$htmlstr .= "   </tr></thead><tbody>\n";
	$i = 0;
	foreach ($dim->data AS $data)
	{
		//$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
		$htmlstr .= "   <tr>\n";
		foreach ($data AS $cell)
			$htmlstr .= "       <td>".$cell."</td>".$nl;
		$htmlstr .= "   </tr>".$nl;
		$i++;
	}
	$htmlstr .= "</tbody></table>".$nl;
	//var_dump($dim->data);
		
    $htmlstr .= "<div class='inserterror'>".$errorstr."</div>";

	echo $htmlstr;
	echo $reloadstr;
?>
</body>
</html>
