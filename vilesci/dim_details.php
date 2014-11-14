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
	require_once('../../../config/vilesci.config.inc.php');
	require_once('../../../include/globals.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/benutzerberechtigung.class.php');
	require_once('dim.class.php');
	
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

	$dim = new dim();
	$dim->dim_id		= 0;
	$dim->diq_id		= $_REQUEST['diq_id'];
	$dim->diq_attribute 	= 'sync.vw_di_';
	$dim->fhc_attribute 	= '';
	$dim->fhc_datatype 	= '';
	
	// ************ INSERT NEW Mapping **********************
	if(isset($_POST['insert']))
	{
		if(!$rechte->isBerechtigt('addon/datenimport', null, 'i'))
			die('Sie haben keine Berechtigung fuer diese Aktion');
	
		if ($_POST['insert']=='new')
		{
			$dim->insertvon		= $user;
			$dim->diq_attribute 	= $_REQUEST['diq_attribute'];
			$dim->fhc_attribute 	= $_REQUEST['fhc_attribute'];
			//$dim->fhc_datatype 	= $_REQUEST['fhc_datatype'];
			if(!$dim->save())
			{
				$errorstr.= $dim->errormsg;
			}
		}
	}
	
	// ************ UPDATE Mapping **********************
	if(isset($_POST['update']))
	{
		if(!$rechte->isBerechtigt('addon/datenimport', null, 'u'))
			die('Sie haben keine Berechtigung fuer diese Aktion');
		$dim->load($_POST['dim_id']);
		$dim->insertvon		= $user;
		$dim->diq_attribute 	= $_REQUEST['diq_attribute'];
		$dim->fhc_attribute 	= $_REQUEST['fhc_attribute'];
		//$dim->fhc_datatype 	= $_REQUEST['fhc_datatype'];
		if(!$dim->save())
		{
			$errorstr.= $dim->errormsg;
		}
	}
	
	
	// ***************** Load ALL Mappings ***********************
	if(isset($_REQUEST['diq_id']))
	{
		if(!$rechte->isBerechtigt('addon/datenimport', null, 's'))
			die('Sie haben keine Berechtigung fuer diese Aktion');
	
		$dim->loadDIQ((int)$_REQUEST["diq_id"]);
	}

	//var_dump($dim->result);
    $htmlstr .="<div class='kopf'>Data Mappings - DIQ-ID: $dim->diq_id</div><br />\n"; 
	$htmlstr .= "	<table class='detail'>\n";
	foreach ($dim->result AS $row)
	{
		$htmlstr .= "		<form id='dimform$row->dim_id' name='dimform$row->dim_id' action='dim_details.php' method='POST'>\n";
		$htmlstr .= "			<tr>\n";
		$htmlstr .= "				<td>DIM Attr.</td>\n";
		$htmlstr .= "				<td><input class='detail' type='text' name='diq_attribute' size='42' maxlength='512' value='".$row->diq_attribute."' onkeydown='change(\"submit$row->dim_id\")'></td>\n";
		$htmlstr .= "				<td>FHC Attr.</td>\n";
		$htmlstr .= "				<td><input class='detail' type='text' name='fhc_attribute' size='32' maxlength='512' value='".$row->fhc_attribute."' onkeydown='change(\"submit$row->dim_id\")'></td>\n";
		//$htmlstr .= "				<td>FHC DataType</td>\n";
		//$htmlstr .= "				<td><input class='detail' type='text' name='fhc_datatype' size='16' maxlength='32' value='".$row->fhc_datatype."' onkeydown='change(\"submit$row->dim_id\")'></td>\n";
		$htmlstr .= "				<td><input id='submit$row->dim_id' type='submit' value='save' name='update' style='visibility:hidden'></td>\n";
		$htmlstr .= "				<input type='hidden' name='diq_id' value='$row->diq_id'>";
		$htmlstr .= "				<input type='hidden' name='dim_id' value='$row->dim_id'>";
		$htmlstr .= "			</tr>\n";
		$htmlstr .= "		</form>\n";
	}
	$htmlstr .= "		<form action='dim_details.php' method='POST' name='dimform'>\n";
	$htmlstr .= "			<tr>\n";
	$htmlstr .= "				<td>DIQ Attr.</td>\n";
	$htmlstr .= "				<td><input class='detail' type='text' name='diq_attribute' size='42' maxlength='512' value='".$dim->diq_attribute."'></td>\n";
	$htmlstr .= "				<td>FHC Attr.</td>\n";
	$htmlstr .= " 				<td><input class='detail' type='text' name='fhc_attribute' size='32' maxlength='512' value='".$dim->fhc_attribute."' onkeydown='change(".'"submit"'.")'></td>\n";
	//$htmlstr .= "				<td>FHC DataType</td>\n";
	//$htmlstr .= " 			<td><input class='detail' type='text' name='fhc_datatype' size='16' maxlength='32' value='".$dim->fhc_datatype."' onkeydown='change()'></td>\n";
	$htmlstr .= "				<td><input type='submit' value='new' name='insert'></td>\n";
	$htmlstr .= "				<input type='hidden' name='diq_id' value='$dim->diq_id'>";
	$htmlstr .= "			</tr>\n";
	$htmlstr .= "		</form>\n";
	$htmlstr .= "	</table>\n";
	$htmlstr .= "<br>\n";
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>"
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Mapping - Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
<script src="../../../include/js/mailcheck.js"></script>
<script src="../../../include/js/datecheck.js"></script>
<script type="text/javascript">
function unchanged()
{
		document.diqform.reset();
		document.diqform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
		checkrequired(document.diqform.diq_id);
}


function change(id)
{
	document.getElementById(id).style.visibility="visible";
}
</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $errorstr;
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>
