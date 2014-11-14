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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */
/**
 * Klasse Datenimport Quelle
 * @create 13-03-2006
 */
require_once('../../../include/basis_db.class.php');

class diq extends basis_db
{
	public $new=true;			//  boolean
	public $result = array();	//  adresse Objekt

	//Tabellenspalten
	public $diq_id;			//  integer
	public $diq_tablename;	//  varchar(32)
	public $diq_keyattribute;	//  varchar (512)
	public $diq_viewname;	//  varchar (32)
	public $diq_view;		//  text
	public $diq_order;		//  integer
	public $diq_limit;		//  integer
	public $csv_uri; 		//  text
	public $csv_tab; 		//  varchar(8)
	public $ods_uri;		//  text
	public $db_typ;			//  varchar(32)
	public $db_host;        //  varchar(256)
	public $db_port;		//  integer
	public $db_name;      	//  varchar(64)
	public $db_user;		//  varchar(64)
	public $db_passwd;		//  varchar(64)
	public $sql;			//  text
	public $updateamum;		//  timestamp
	public $updatevon;		//  string
	public $insertamum;     //  timestamp
	public $insertvon;      //  string
	
	public $diq_num_rows;	//  integer
	public $data;			//Datenobjekt
	public $num_fields;
	public $num_rows;
	public $field_names=array();

	/**
	 * Konstruktor
	 * @param $adress_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->new=true;
	}

	/**
	 * Laedt die Datenimportquelle mit der ID $diq_id
	 * @param  $adress_id ID der zu ladenden Adresse
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($diq_id)
	{
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($diq_id) || $diq_id == '')
		{
			$this->errormsg = 'DI_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM addon.tbl_di_quelle WHERE diq_id=".$this->db_add_param($diq_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{	
			$this->diq_id			= $row->diq_id;
			$this->diq_tablename 	= $row->diq_tablename;
			$this->diq_keyattribute	= $row->diq_keyattribute;
			$this->diq_viewname		= $row->diq_viewname;
			$this->diq_view			= $row->diq_view;
			$this->diq_order		= $row->diq_order;
			$this->diq_limit		= $row->diq_limit;
			$this->csv_uri			= $row->csv_uri;
			$this->csv_tab			= $row->csv_tab;
			$this->ods_uri			= $row->ods_uri;
			$this->db_typ			= $row->db_typ;
			$this->db_host			= $row->db_host;
			$this->db_port			= $row->db_port;
			$this->db_name			= $row->db_name;
			$this->db_user		= $row->db_user;
			$this->db_passwd		= $row->db_passwd;
			$this->sql				= $row->sql;
			//$this->updateamum		= $row->updateamum;
			//$this->updatevon		= $row->updatevon;
			//$this->insertamum		= $row->insertamum;
			//$this->insertvon		= $row->insertvon;
			$this->new=false;
		}
		else
		{
			$this->errormsg = 'Es ist keine Datenimportquelle mit dieser ID vorhanden!';
			return false;
		}

		return true;
	}
	
	/**
	 * Laedt die Daten mit der ID $diq_id
	 * @param  $adress_id ID der zu ladenden Adresse
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadData($tablename,$keyattribute, $keyvalue)
	{
		//Daten aus der Datenbank lesen
		$qry = 'SELECT * FROM '.$tablename.' WHERE '.$keyattribute."='".$keyvalue."';";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage!';
			return false;
		}
		if ($this->db_num_rows()>1)
		{
			$this->errormsg = 'Zuviele Datensaetze entsprechen dem Key!';
			return false;
		}
		if ($this->db_num_rows()<1)
		{
			$this->errormsg = 'Datensatz nicht gefnunden!';
			return 0;
		}
		$this->num_rows=$this->db_num_rows();
		$this->num_fields=$this->db_num_fields();
		if($row = $this->db_fetch_row())
		{	
			$this->data=$row;
			return 1;
		}
		else
		{
			$this->errormsg = 'Es ist keine Datenimportquelle mit dieser ID vorhanden!';
			return false;
		}

		return true;
	}
	
	/**
	 * Laedt alle Adressen zu der Person die uebergeben wird
	 * @param $pers_id ID der Person zu der die Adressen geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadAll()
	{

		//Lesen der Daten aus der Datenbank
		$qry = 'SELECT * FROM addon.tbl_di_quelle ORDER BY diq_order, diq_id;';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$obj = new diq();

			$obj->diq_id		= $row->diq_id;
			$obj->diq_tablename = $row->diq_tablename;
			$obj->diq_viewname	= $row->diq_viewname;
			$obj->diq_view		= $row->diq_view;
			$obj->diq_order		= $row->diq_order;
			$obj->diq_limit		= $row->diq_limit;
			$obj->csv_uri		= $row->csv_uri;
			$obj->csv_tab		= $row->csv_tab;
			$obj->ods_uri		= $row->ods_uri;
			$obj->db_typ		= $row->db_typ;
			$obj->db_host		= $row->db_host;
			$obj->db_port		= $row->db_port;
			$obj->db_name		= $row->db_name;
			$obj->db_user		= $row->db_user;
			$obj->db_passwd		= $row->db_passwd;
			$obj->sql			= $row->sql;
			$obj->diq_keyattribute	= $row->diq_keyattribute;
			$obj->diq_num_rows	= $this->getNumRows('sync.'.$row->diq_tablename);
			//$obj->updateamum    = $row->updateamum;
			//$obj->updatevon     = $row->updatevon;
			//$obj->insertamum    = $row->insertamum;
			//$obj->insertvon     = $row->insertvon;
			$obj->new       = false;

			$this->result[] = $obj;
		}
		return true;
	}
	
	/**
	 * Ermitteln die Anzahl der Datensaetze
	 * @return num_rows
	 */
	public function getNumRows($table)
	{
		// Neues DB-Objekt
		$dbo=new basis_db();
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT count(*) AS num_rows FROM $table;";

		if(!@$dbo->db_query($qry))
		{
			$dbo->errormsg = 'Fehler bei einer Datenbankabfrage';
			return 0;
		}
		
		if(!$row = $dbo->db_fetch_object())
			return 0;
		else
			return $row->num_rows;
	}

	/**
	 * Ermitteln die Anzahl der Datensaetze
	 * @return num_rows
	 */
	public function getCountStatus($status)
	{
		// Neues DB-Objekt
		$dbo=new basis_db();
		//Lesen der Daten aus der Datenbank
		$qry = 'SELECT count(*) AS num_rows FROM sync.'.$this->diq_tablename." WHERE status='$status';";

		if(!@$dbo->db_query($qry))
		{
			$dbo->errormsg = 'Fehler bei einer Datenbankabfrage';
			return 0;
		}
		
		if(!$row = $dbo->db_fetch_object())
			return 0;
		else
			return $row->num_rows;
	}
	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->db_port) && $this->db_port!='')
		{
			$this->errormsg='db_port enthaelt ungueltige Zeichen';
			return false;
		}
		//Gesamtlaenge pruefen
		if(mb_strlen($this->diq_tablename)>255)
		{
			$this->errormsg = 'TableName darf nicht länger als 255 Zeichen sein';
			return false;
		}
		

		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO addon.tbl_di_quelle (diq_tablename, diq_viewname,
					diq_view, diq_order, diq_limit, csv_uri, csv_tab, ods_uri, db_typ, db_host, db_port,
					db_name, db_user, db_passwd, sql, diq_keyattribute,
					insertamum, insertvon) VALUES('.
			      $this->db_add_param($this->diq_tablename).', '.
			      $this->db_add_param($this->diq_viewname).', '.
			      $this->db_add_param($this->diq_view).', '.
			      $this->db_add_param($this->diq_order).', '.
			      $this->db_add_param($this->diq_limit).', '.
			      $this->db_add_param($this->csv_uri).', '.
			      $this->db_add_param($this->csv_tab).', '.
			      $this->db_add_param($this->ods_uri).', '.
			      $this->db_add_param($this->db_typ).', '.
			      $this->db_add_param(trim($this->db_host)).', '.
			      $this->db_add_param($this->db_port).', '.
			      $this->db_add_param($this->db_name).', '.
			      $this->db_add_param($this->db_user).', '.
			      $this->db_add_param($this->db_passwd).', '.
			      $this->db_add_param($this->sql).', '.
			      $this->db_add_param($this->diq_keyattribute).', now(), '.
			      $this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob adresse_id eine gueltige Zahl ist
			if(!is_numeric($this->diq_id))
			{
				$this->errormsg = 'diq_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE addon.tbl_di_quelle SET'.
				' diq_tablename='.$this->db_add_param($this->diq_tablename).', '.
				' diq_viewname='.$this->db_add_param($this->diq_viewname).', '.
				' diq_view='.$this->db_add_param($this->diq_view).', '.
				' diq_order='.$this->db_add_param($this->diq_order).', '.
				' diq_limit='.$this->db_add_param($this->diq_limit).', '.
				' csv_uri='.$this->db_add_param($this->csv_uri).', '.
				' csv_tab='.$this->db_add_param($this->csv_tab).', '.
				' ods_uri='.$this->db_add_param($this->ods_uri).', '.
				' db_typ='.$this->db_add_param($this->db_typ).', '.
		      	' db_host='.$this->db_add_param(trim($this->db_host)).', '.
		      	' db_port='.$this->db_add_param($this->db_port).', '.
		      	' db_name='.$this->db_add_param($this->db_name).', '.
		      	' db_user='.$this->db_add_param($this->db_user).', '.
		      	' db_passwd='.$this->db_add_param($this->db_passwd).','.
		      	' sql='.$this->db_add_param($this->sql).', '.
		      	' diq_keyattribute='.$this->db_add_param($this->diq_keyattribute).', '.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).
		      	' WHERE diq_id='.$this->db_add_param($this->diq_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('addon.tbl_di_quelle_diq_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->diq_id = $row->id;
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}

		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des DI-Datensatzes';
			return false;
		}
		return $this->diq_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $diq_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($diq_id)
	{
		//Pruefen ob adresse_id eine gueltige Zahl ist
		if(!is_numeric($diq_id) || $diq_id == '')
		{
			$this->errormsg = 'diq_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM addon.tbl_di_quelle WHERE diq_id=".$this->db_add_param($diq_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten'."\n";
			return false;
		}
	}
	
	/**
	 * Setzten des Status der importierten Daten
	 * @param $diq_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveStatus($id,$status)
	{
		//Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($id) || $id == '')
		{
			$this->errormsg = 'id muss eine gueltige Zahl sein'."\n";
			return false;
		}

		// Status setzen
		$qry='UPDATE sync.'.$this->diq_tablename.' SET status='.$this->db_add_param($status, FHC_STRING, false).' WHERE id='.$this->db_add_param($id, FHC_INTEGER, false).';';

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = "Fehler beim Setzen des Status\n";
			return false;
		}
	}
}
?>
