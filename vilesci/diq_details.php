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
	require_once('diq.class.php');
	
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

	$diq = new diq();
	$diq->diq_id		= 0;
	$diq->diq_tablename 	= 'tbl_di_';
	$diq->diq_keyattribute= '';
	$diq->diq_viewname= 'vw_di_';
	$diq->diq_view= 'SELECT ';
	$diq->csv_uri		= '';
	$diq->csv_tab		= ';';
	$diq->ods_uri		= '';
	$diq->db_typ		= '';
	$diq->db_host		= '';
	$diq->db_port		= '';
	$diq->db_name		= '';
	$diq->db_user		= '';
	$diq->db_passwd		= '';
	$diq->sql			= '';
	$diq->insertvon		= $user;
	$diq->updatevon		= $user;
	
	
	if(isset($_REQUEST["action"]) && isset($_REQUEST["diq_id"]))
	{
		if(!$rechte->isBerechtigt('addon/datenimport', null, 'suid'))
			die('Sie haben keine Berechtigung fuer diese Aktion');
	
		// echo 'DI_ID: '.var_dump((int)$_POST["diq_id"]);
		// Wenn id > 0 ist -> Neuer Datensatz; ansonsten load und update
		if ( ((int)$_REQUEST["diq_id"]) > 0)
			$diq->load((int)$_REQUEST["diq_id"]);
		if ($_REQUEST["action"]=='Save')
		{
			$diq->diq_tablename = $_POST["diq_tablename"];
			$diq->diq_keyattribute = $_POST["diq_keyattribute"];
			$diq->diq_viewname = $_POST["diq_viewname"];
			$diq->diq_view = $_POST["diq_view"];
			$diq->diq_order = $_POST["diq_order"];
			$diq->diq_limit = $_POST["diq_limit"];
			$diq->csv_uri = $_POST["csv_uri"];
			$diq->csv_tab = $_POST["csv_tab"];
			$diq->ods_uri = $_POST["ods_uri"];
			$diq->db_typ = $_POST["db_typ"];
			$diq->db_host = $_POST["db_host"];
			$diq->db_port = $_POST["db_port"];
			$diq->db_name = $_POST["db_name"];
			$diq->db_user = $_POST["db_user"];
			$diq->db_passwd = $_POST["db_passwd"];
			$diq->sql = $_POST['sql'];
		
			if(!$diq->save())
			{
				$errorstr .= $diq->errormsg;
			}
		
			$reloadstr .= "<script type='text/javascript'>\n";
			$reloadstr .= "	parent.uebersicht_diq.location.href='diq_uebersicht.php';";
			$reloadstr .= "</script>\n";
		}
		if ($_REQUEST["action"]=='SaveView')
		{
			$qry='CREATE OR REPLACE VIEW sync.'.$diq->diq_viewname.' AS '.$diq->diq_view.';';
			if(!$diq->db_query($qry))
			{
				$errorstr .= $diq->errormsg;
			}
			else
				$htmlstr.='<div align="right">View '.$diq->diq_viewname.' erfolgreich gespeichert!</div>';
		}
		if ($_REQUEST["action"]=='ExportView')
		{
			$qry='SELECT * FROM sync.'.$diq->diq_viewname.';';
			if(!$diq->db_query($qry))
			{
				$errorstr .= $diq->errormsg;
			}
			else
			{
				//if (numberOfElements($diq->data) == 0) return null;
				$n=$diq->db_num_fields();
				for ($i=0; $i<$n; $i++)
					$names[]=$diq->db_field_name(null,$i);
				ob_start();
				$df = fopen("php://output", 'w');
				fputcsv($df, $names, $diq->csv_tab);
				while ($row = $diq->db_fetch_row())
				{
					//var_dump($row);
					fputcsv($df, $row, $diq->csv_tab);
				}
				fclose($df);
				$out=ob_get_clean();
				// Download ***************
				// disable caching
				$now = gmdate("D, d M Y H:i:s");
				header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
				header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
				header("Last-Modified: {$now} GMT");
				// force download  
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");

				// disposition / encoding on response body
				header('Content-Disposition: attachment;filename='.$diq->diq_viewname.'.csv');
				header("Content-Transfer-Encoding: binary");
				echo $out;
				exit;			
			}
		}
	}



	if ((isset($_REQUEST['diq_id'])) && ((!isset($_REQUEST['neu'])) || ($_REQUEST['neu']!= "true")))
	{
		$diq->load($_REQUEST["diq_id"]);
		if ($diq->errormsg!='')
			die($diq->errormsg);
	}
		
    if($diq->diq_id != '')
        $htmlstr .= "<br><div class='kopf'>DI-Quelle <b>".$diq->diq_id."</b></div>\n";
    else
        $htmlstr .="<br><div class='kopf'>Neue Quelle</div>\n"; 
	$htmlstr .= "<form action='diq_details.php' method='POST' name='diqform'>\n";
	$htmlstr .= "	<table class='detail'>\n";
	$htmlstr .= "	  <tr>\n";
	$htmlstr .= "		<td>TableName</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='diq_tablename' size='22' maxlength='32' value='".$diq->diq_tablename."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>KeyAttribute</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='diq_keyattribute' size='8' maxlength='512' value='".$diq->diq_keyattribute."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>Order</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='diq_order' size='2' maxlength='2' value='".$diq->diq_order."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>DB Type</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='db_typ' size='8' maxlength='16' value='".$diq->db_typ."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>DB Host</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='db_host' size='16' maxlength='32' value='".$diq->db_host."' onchange='submitable()'></td>\n";
	$htmlstr .= "	  </tr>\n";
	$htmlstr .= "	  <tr>\n";
	$htmlstr .= "		<td>CSV URI</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='csv_uri' size='32' maxlength='1024' value='".$diq->csv_uri."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>CSV DELIM</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='csv_tab' size='4' maxlength='4' value='".$diq->csv_tab."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>Limit</td>\n";
	$htmlstr .= " 		<td><input class='detail' type='text' name='diq_limit' size='6' maxlength='6' value='".$diq->diq_limit."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>DB Port</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='db_port' size='8' maxlength='5' value='".$diq->db_port."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>DB Name</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='db_name' size='8' maxlength='32' value='".$diq->db_name."' onchange='submitable()'></td>\n";
	$htmlstr .= "	  </tr>\n";
	$htmlstr .= "	  <tr>\n";
	$htmlstr .= "		<td>ODS/XML URI</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='ods_uri' size='32' maxlength='1024' value='".$diq->ods_uri."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>View Name</td>\n";
	$htmlstr .= "		<td colspan='2'><input class='detail' type='text' name='diq_viewname' size='22' maxlength='64' value='".$diq->diq_viewname."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td></td><td>DB User</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='db_user' size='8' maxlength='32' value='".$diq->db_user."' onchange='submitable()'></td>\n";
	$htmlstr .= "		<td>DB Passwd</td>\n";
	$htmlstr .= "		<td><input class='detail' type='text' name='db_passwd' size='8' maxlength='32' value='".$diq->db_passwd."' onchange='submitable()'></td>\n";
	$htmlstr .= "	  </tr>\n";
	$htmlstr .= "	  <tr>\n";
	$htmlstr .= "		<td valign='top'>Mapping VIEW
					<a href='diq_details.php?diq_id=".$diq->diq_id."&action=SaveView' >
						<img title='CREATE OR REPLACE VIEW ".$diq->diq_viewname."!' src='view-refresh.png' />
					</a><br />
					<input type='submit' value='ExportView' name='action'>\n
				</td>\n";
	$htmlstr .= " 		<td colspan='4'><textarea name='diq_view' cols='70' rows='6' onchange='submitable()'>".$diq->diq_view."</textarea></td>\n";
	$htmlstr .= "		<td valign='top'>SQL / XSLT</td>\n";
	$htmlstr .= " 		<td colspan='4'><textarea name='sql' cols='70' rows='6' onchange='submitable()'>".$diq->sql."</textarea></td>\n";
	$htmlstr .= "	  </tr>\n";
	$htmlstr .= "	</table>\n";
	$htmlstr .= "<br>\n";
	$htmlstr .= "<div align='right' id='sub'>\n";
	$htmlstr .= "	<span id='submsg' style='color:red; visibility:hidden;'>Datensatz ge&auml;ndert!&nbsp;&nbsp;</span>\n";
	$htmlstr .= "	<input type='hidden' name='diq_id' value='".$diq->diq_id."'>";
	$htmlstr .= "	<input type='submit' value='Save' name='action'>\n";
	$htmlstr .= "	<input type='button' value='Reset' onclick='unchanged()'>\n";
	$htmlstr .= "</div>";
	$htmlstr .= "</form>";
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>"
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>DI-Quelle - Details</title>
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

function checkrequired(feld)
{
	if(feld.value == '')
	{
		feld.className = "input_error";
		return false;
	}
	else
	{
		feld.className = "input_ok";
		return true;
	}
}

function submitable()
{
	required1 = checkrequired(document.diqform.diq_id);

	if(!required1)
	{
		document.diqform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
	}
	else
	{
		document.diqform.schick.disabled = false;
		document.getElementById("submsg").style.visibility="visible";
	}
}
</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>
