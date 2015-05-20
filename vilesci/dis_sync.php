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

ini_set('memory_limit', '1024M');
ini_set('max_execution_time','1200');
 
?>
 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Datensynchro</title>
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
	$num_iu=0;

	
	
	// ***************** Start Sync ***********************
	if(!isset($_REQUEST['diq_id']))
		die ('diq_id is not set!');
	if(!$rechte->isBerechtigt('addon/datenimport', null, 's'))
		die('Sie haben keine Berechtigung fuer diese Aktion');
	
	$diq = new diq();
	$diq->load((int)$_REQUEST["diq_id"]);
	$dim = new dim();
	$dim->loadDIQ((int)$_REQUEST["diq_id"]);
	echo "<div class='kopf'>Data Synchro - DIQ-ID: ".$_REQUEST['diq_id']."</div><br /><br />\n"; 
	
	$diq_table=$dim->getDiqTable();
	$fhc_table=$dim->getFhcTable();
	// ***************** UPDATES *********************
	$where='';
	foreach ($dim->dataobject AS $map)
	{
		if ($map->diq_attribute!='')
			switch (substr($map->fhc_datatype,0,4))
			{
				case  'date':
					//if (preg_match('#^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$#',$map->diq_attribute))
						$where.=' OR '.$map->diq_attribute.'::'.$map->fhc_datatype.'!='.$map->fhc_attribute.'';
					break;
				case 'time': // timestamp
					$where.=' OR COALESCE('.$map->diq_attribute.',\'1970-01-01\')::'.$map->fhc_datatype.'!=COALESCE('.$map->fhc_attribute.',\'1970-01-01\')';
					break;
				case  'char':
				case  'varc':
				case  'text':
					$where.=' OR COALESCE('.$map->diq_attribute."::varchar,'')::".$map->fhc_datatype.'!=COALESCE('.$map->fhc_attribute.",'')";
					break;
				case 'bigi':
					$where.=' OR COALESCE('.$map->diq_attribute.'::'.$map->fhc_datatype.',0)::'.$map->fhc_datatype.'!=COALESCE('.$map->fhc_attribute.',0)';
					break;
				case 'nume':
				case 'inte':
				case 'smal':
					$where.=' OR CASE WHEN COALESCE('.$map->diq_attribute."::varchar,'')='' THEN NULL ELSE ".$map->diq_attribute.' END::'.$map->fhc_datatype.'!=COALESCE('.$map->fhc_attribute.',0)';
					break;
				default:
					$where.=' OR '.$map->diq_attribute.'::'.$map->fhc_datatype.'!='.$map->fhc_attribute.'';
			}
	}
	$where=substr($where,4);
	$qry="SELECT $diq_table.* FROM $diq_table JOIN $fhc_table ON (id=ext_id) WHERE (status='i' OR status='u') AND ($where)";
	if (!is_null($diq->diq_limit))
		$qry.=" LIMIT $diq->diq_limit;";
	else
		$qry.=";";
	echo 'Updateing (<span title="'.$qry.'">Query</span>):<br />';
	//echo $qry;
	ob_flush();
	flush();
	if(!$dim->loadData($qry))
		die($qry);
	foreach ($dim->data AS $data)
	{
		$qry="UPDATE $fhc_table SET ";
		foreach($dim->dataobject AS $dataobject)
		{
			//echo substr($dataobject->fhc_datatype,0,4).'<BR>';
			if ($data[$dataobject->diq_column]==='')
			{
				if (substr($dataobject->fhc_datatype,0,4)!='char' && substr($dataobject->fhc_datatype,0,4)!='varc' && substr($dataobject->fhc_datatype,0,4)!='text')
					$qry.=$dataobject->fhc_column."=NULL, ";
				else    
					$qry.=$dataobject->fhc_column."='".$dim->db_escape($data[$dataobject->diq_column])."', ";
			} 
            elseif ($data[$dataobject->diq_column]==null)
				$qry.=$dataobject->fhc_column."=NULL, ";
			else
				$qry.=$dataobject->fhc_column."='".$dim->db_escape($data[$dataobject->diq_column])."', ";
		}
		$qry=substr($qry,0,-2); // letztes Komma wieder loeschen
		$qry.=' WHERE ext_id='.$data['id'].';';
		//echo $qry;die();
		if (!$db->db_query($qry))
		{
			$errorstr.=$db->db_last_error();
			if(!$diq->saveStatus($data['id'],'e'))
				$errorstr.=$diq->errormsg;
		}
		else
		{
			// Status auf s setzen
			if(!$diq->saveStatus($data['id'],'s'))
				$errorstr.=$diq->errormsg;
			else
				echo '<span title="'.$qry.'">u</span>';
		}
		$num_iu++;
		ob_flush();
		flush();
	}
	//var_dump($dim->data);
	// ***************** INSERTS *********************
	//$qry="SELECT * FROM $diq_table WHERE (status='i' OR status='u') AND id NOT IN (SELECT COALESCE(ext_id,-1) FROM $fhc_table WHERE ext_id IS NOT NULL)";
	$qry="SELECT $diq_table.* FROM $diq_table LEFT OUTER JOIN $fhc_table ON (ext_id=id) WHERE (status='i' OR status='u') AND ext_id IS NULL";
	if (!is_null($diq->diq_limit))
		$qry.=" LIMIT $diq->diq_limit;";
	else
		$qry.=";";
	echo '<br/>Inserting (<span title="'.$qry.'">Query</span>):<br />';
	//echo $qry;
	ob_flush();
	flush();
	if(!$dim->loadData($qry))
		die($qry);
	foreach ($dim->data AS $data)
	{
		$columns='';
		$values='';
		foreach($dim->dataobject AS $dataobject)
		{
			$columns.=$dataobject->fhc_column.', ';
			//echo substr($dataobject->fhc_datatype,0,4).'<BR>';
			if ($data[$dataobject->diq_column]==='')
			{
				if (substr($dataobject->fhc_datatype,0,4)!='char' && substr($dataobject->fhc_datatype,0,4)!='varc' && substr($dataobject->fhc_datatype,0,4)!='text')
					$values.="NULL, ";
				else    
					$values.="'".$dim->db_escape($data[$dataobject->diq_column])."', ";
			}  
			elseif ($data[$dataobject->diq_column]==null)
				$values.="NULL, ";
			else
				$values.="'".$dim->db_escape($data[$dataobject->diq_column])."', ";
		}
		$columns.='ext_id';		// substr($columns,0,-2); // letztes Komma wieder loeschen
		$values.=$data['id'];		//substr($values,0,-2);
		$qry="INSERT INTO $fhc_table ($columns) VALUES ($values);";
		//echo $qry;
		if (!$db->db_query($qry))
		{
			$errorstr.=$db->db_last_error().' - '.$qry;
			if(!$diq->saveStatus($data['id'],'e'))
				$errorstr.=$diq->errormsg;
		}
		else
		{
			// Status auf s setzen
			if(!$diq->saveStatus($data['id'],'s'))
				$errorstr.=$diq->errormsg;
			else
				echo 'i';
		}
		$num_iu++;
		ob_flush();
		flush();
	}
	
	/*// Alle u und i auf s setzen, die haben anscheinend keinen Unterschied
	if ($num_iu==0)
	{
		$qry='UPDATE sync.'.$diq->diq_tablename." SET status='s' WHERE status='u' OR status='i';";
		$diq->db_query($qry);
	}*/
	
    $htmlstr .= "<div class='inserterror'>".$errorstr."</div>";

	echo $htmlstr;
	echo $reloadstr;
?>
</body>
</html>
