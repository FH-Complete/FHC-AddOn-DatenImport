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
 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Datenimport</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
		<script src="../../../include/js/mailcheck.js"></script>
		<script src="../../../include/js/datecheck.js"></script>
	</head>
	<body style="background-color:#eeeeee;">
	<?php
	require_once('../../../config/vilesci.config.inc.php');
	require_once('../../../include/globals.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/benutzerberechtigung.class.php');
	require_once('diq.class.php');
	static $db;
	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
	$count=0;
	$inserts=0;
	$updates=0;
	$offset=3;
	$user = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('addon/datenimport'))
		die('Sie haben keine Berechtigung fuer dieses AddOn!');
	
	if (!isset($_REQUEST['diq_id']))
		die ('diq_id not set!');

	// Datenquelle laden
	$diq = new diq();
	$diq->load((int)$_REQUEST['diq_id']);
	
	if(!$rechte->isBerechtigt('addon/datenimport', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');
	
	// Tabelle pruefen und ggf. anlegen
	function checktable($tablename)
	{
		global $db;
		if(!$result = @$db->db_query('SELECT 1 FROM sync.'.$tablename.';'))
		{
			$qry = 'CREATE TABLE sync.'.$tablename.'
			(
				id serial,
				status char,
				lastupdate timestamp
			);
			GRANT SELECT, UPDATE, INSERT, DELETE ON sync.'.$tablename.' TO vilesci;';

			if(!$db->db_query($qry))
				echo '<strong>sync.'.$tablename.': '.$db->db_last_error().'</strong><br />';
			else 
				echo ' sync.'.$tablename.': Tabelle '.$tablename.' hinzugefuegt!<br />';
		}
		echo 'Tabelle '.$tablename.' ist vorhanden! ;-)<br />';
	}
	
	// Attribut pruefen und ggf. anlegen
	function checkattribute($tablename, $attribute, $datatype='varchar(512)')
	{
		global $db;
		if(!$result = @$db->db_query("SELECT $attribute FROM sync.$tablename"))
		{
			$qry = 'ALTER TABLE sync.'.$tablename.' ADD COLUMN '.$attribute.' '.$datatype.';';
			//echo $qry;
			if(!$db->db_query($qry))
				echo '<strong>sync.'.$tablename.': '.$db->db_last_error().'</strong><br />';
			else 
				echo ' sync.'.$tablename.': Attribut sync.'.$tablename.'.'.$attribute.' hinzugefuegt!<br />';

		}
	}
	checktable($diq->diq_tablename);
	
	// ************* Import *********************
	$words=array();
	$attributes='';
	$notin='';
	$keyindex=-1;
	$i=0;
	class importdata
	{
		public $data=array();
		public $attribute=array();
		public $datatype=array();
	}
	$importdata=new importdata();
		
	// ================== SQL-Import ===================================
	if ($diq->db_typ!='' && $diq->db_typ!=null)
	{
		// Connect to DB-System
		$num_rows=0;
		$conn_str='host='.$diq->db_host.' port='.$diq->db_port.' dbname='.$diq->db_name.' user='.$diq->db_user.' password='.$diq->db_passwd;
		switch ($diq->db_typ)
		{
			case 'mssql':
				if(!$conn=mssql_connect($diq->db_host, $diq->db_user, $diq->db_passwd))
					die ('Cannot connect to MSSQL DB-System! -> '.$diq->db_host.' - '.$diq->db_user.' - '.$diq->db_passwd);
				if(!mssql_select_db($diq->db_name,$conn))
					die ('Cannot connect to MSSQL Database: '.$diq->db_name);
				mssql_query('SET CONCAT_NULL_YIELDS_NULL ON');
				if(!$result=mssql_query($diq->sql))
					die ('MSSQL Error'.mssql_get_last_message());
				//Fieldnames
				for ($i=0;$i<mssql_num_fields($result);$i++)
					$importdata->attribute[$i]=mssql_field_name($result,$i);
				//Daten
				while ($importdata->data[]=mssql_fetch_row($result))
					;
				$num_rows=mssql_num_rows($result);
				break;
			case 'pgsql':
				if(!$conn=pg_connect($conn_str))
					die ('Cannot connect to PostgreSQL DB-System!');
				if(!$result=pg_query($diq->sql))
					die ('PostgreSQL Error'.pg_last_error());
				//Fieldnames
				for ($i=0;$i<pg_num_fields($result);$i++)
					$importdata->attribute[$i]=pg_field_name($result,$i);
				//Daten
				while ($importdata->data[]=pg_fetch_row($result))
					;
				$num_rows=pg_num_rows($result);
				//var_dump($importdata);
				break;
		}
		// Attribute pruefen
		$i=0;
		foreach ($importdata->attribute AS $w)
		{
			$w=strtolower(str_replace('-','',str_replace(' ','',trim($w))));
			if ($w==$diq->diq_keyattribute)
			{
				$keyindex=$i;
				checkattribute($diq->diq_tablename, $w);
			}
			else
				checkattribute($diq->diq_tablename, $w, 'text');
			$attributes.=$w.',';				
			//echo $w.'=='.$diq->diq_keyattribute.'<br />';
			$i++;
		}	
		$attributes=substr($attributes,0,-1);
		// Daten importieren
		$i=0;
		echo '&nbsp;0%&nbsp;';
		foreach ($importdata->data AS $words)
		{
			if (!is_array($words))
				break;
			$notin.="'".$words[$keyindex]."',";
			// Datensatz zum vergleich holen
			if(!$res=$diq->loadData('sync.'.$diq->diq_tablename,$diq->diq_keyattribute,$words[$keyindex]))
			{
				if ($res==0)
				{
					// Neuer Datensatz **********************
					$qry='INSERT INTO sync.'.($diq->diq_tablename).' (status,lastupdate,'.$attributes.") VALUES ('i',now(),";
					foreach ($words AS $w)
					{
						$qry.="'".$diq->db_escape($w)."',";
					}
					$qry=substr($qry,0,-1);
					$qry.=');';
					//echo $qry;
					if(!@$db->db_query($qry))
						echo '<strong>sync.'.$diq->diq_tablename.': '.$db->db_last_error().'<br />'.$qry.'</strong><br />';
					else 
						echo 'i';
				}
			}
			else
			{
				if ($res==1)
				{
					// Datensatz UPDATE ***************
					$update=false;
					$qry='UPDATE sync.'.($diq->diq_tablename)." SET lastupdate=now(), status='u'";
					for ($j=0;$j<count($words);$j++)
					{
						//echo $j;
						if ((string)$diq->db_escape($words[$j])!=$diq->data[$j+$offset])
						{
							//echo '<br />"'.$words[$j].'"!="'.$diq->data[$j+$offset].'"';
							$qry.=', '.$diq->db_field_name(null,$j+$offset)."='".$diq->db_escape($words[$j])."' ";
							$update=true;
						}
					}
					if ($update)
					{
						$qry.=' WHERE '.$diq->diq_keyattribute."='".$words[$keyindex]."';";
						//echo '<br />'.$qry;
						if(!@$db->db_query($qry))
							echo '<strong>sync.'.$diq->diq_tablename.': '.$db->db_last_error().'<br />'.$qry.'</strong><br />';
						else 
							echo '<span title="'.htmlentities($qry).'">u</span>';
						$updates++;
					}
				}
			}
			//var_dump($words[$keyindex]);
			echo '.';
			if (++$i%250==0)
				echo '<br />'.round(100/$num_rows*$i).'%&nbsp;';
			ob_flush();
			flush();
		}
		echo '<br />100%';
	}
	
	// ================== XML-Import ===================================
	if ($diq->ods_uri!='' && $diq->ods_uri!=null)
	{
		die("Not Implemented yet");
		//echo $diq->ods_uri;
		$xsltDoc=new SimpleXMLElement($diq->sql);
		$xmlDoc = new DOMDocument();
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xsltDoc);
		$csv_uri='';
				
		// Files splitten
		$files=str_getcsv($diq->ods_uri);
		$diq->csv_uri='';
		//var_dump($files);
		//Files laden und als csv ausgeben
		foreach ($files AS $f)
		{
			echo '<br />Transforming'.trim($f);
			$xmlDoc->load(trim($f));
			$csv_uri=trim($f).'.csv';
			$fp = fopen($csv_uri, 'w');
			//echo $proc->transformToXML($xmlDoc);
			//die();
			fwrite($fp, $proc->transformToXML($xmlDoc));
			fclose($fp);
			$diq->csv_uri.=$csv_uri.',	';
			ob_flush();
			flush();
		}
		// letztes Zeichen wieder weg
		$diq->csv_uri=substr($diq->csv_uri,0,-2);
	}
	// ================== CSV-Import ===================================
	if ($diq->csv_uri!='' && $diq->csv_uri!=null)
	{
		// Files Laden
		$files=str_getcsv(trim($diq->csv_uri),$diq->csv_tab);
		//var_dump($files);
		foreach ($files AS $f)
		{
			$words=array();
			$attributes='';
			$keyindex=-1;
			$i=0;
			echo '<br />Importing'.trim($f).'<br />';
			$handle = fopen(trim($f), "r"); //dirname(__FILE__).
			//Kopfzeile lesen und Attribte checken ****************
			$head=fgets($handle);
			$words=str_getcsv($head,$diq->csv_tab);
			//var_dump($words);die();
			foreach ($words AS $w)
			{
				$w=strtolower(str_replace('-','',str_replace(' ','',trim($w))));
				checkattribute($diq->diq_tablename, $w);
				$attributes.=$w.',';
				if ($w==$diq->diq_keyattribute)
					$keyindex=$i;
				//echo $w.'=='.$diq->diq_keyattribute.'<br />';
				$i++;
			}	
			$attributes=substr($attributes,0,-1);
			$i=0;
			// Import *********************************************
			while(!feof($handle))
			{
				$line=strtr(fgets($handle),"'","`"); // Steuerzeichen!
				$words=str_getcsv($line,$diq->csv_tab);
				//echo count($words);
				if (count($words)<=1)
					break;
				$count++;
				// Datensatz zum vergleich holen
				$res=$diq->loadData('sync.'.$diq->diq_tablename,$diq->diq_keyattribute,$words[$keyindex]);
				if ($res==0)
				{
					// Neuer Datensatz ********************
					$qry='INSERT INTO sync.'.($diq->diq_tablename).' (status,lastupdate,'.$attributes.") VALUES ('i',now(),";
					foreach ($words AS $w)
					{
						$qry.="'$w',";
					}
					$qry=substr($qry,0,-1);
					$qry.=');';
					//echo $qry;
					if(!@$db->db_query($qry))
						echo '<strong>sync.'.$diq->diq_tablename.': '.$db->db_last_error().'<br />'.$qry.'</strong><br />';
					else 
						echo 'i';
					$inserts++;
				}
				elseif ($res==1)
				{
					// Datensatz UPDATE *******************
					$update=false;
					$qry='UPDATE sync.'.($diq->diq_tablename)." SET lastupdate=now(), status='u'";
					for ($j=0;$j<count($words);$j++)
					{
						//echo $j;
						if ($words[$j]!=$diq->data[$j+$offset])
						{
							$qry.=', '.$diq->db_field_name(null,$j+$offset)."='".$words[$j]."' ";
							$update=true;
						}
					}
					if ($update)
					{
						$qry.=' WHERE '.$diq->diq_keyattribute."='".$words[$keyindex]."';";
						//echo $qry;
						if(!@$db->db_query($qry))
							echo '<strong>sync.'.$diq->diq_tablename.': '.$db->db_last_error().'<br />'.$qry.'</strong><br />';
						else 
							echo '<span title="'.htmlentities($qry).'">u</span>';
						$updates++;
					}
					else
						echo '.';
				}
				
				//var_dump($words[$keyindex]);
				if (++$i%250==0)
					echo '<br />';
				ob_flush();
				flush();
				if ($count==$diq->diq_limit)
				{
					echo '<br />Limit: '.$diq->diq_limit;
					break;
				}
			}
		}
	}
	echo '<br />'.$count.' checks!';
	echo '<br />'.$inserts.' inserts!';
	echo '<br />'.$updates.' updates!';
	if ($notin=substr($notin,0,-1))  // letztes Komma loeschen
	{
		$qry='UPDATE sync.'.($diq->diq_tablename)." SET lastupdate=now(), status='d'";
		$qry.=' WHERE '.$diq->diq_keyattribute." NOT IN (".$notin.");";
		//echo $qry;
		if(!@$db->db_query($qry))
			echo '<strong>sync.'.$diq->diq_tablename.': '.$db->db_last_error().'<br />'.$qry.'</strong><br />';
		else 
			echo '<br />Status d updated!';
	}
	else
		echo '<br />No deletes!';
	?>
	</body>
</html>
