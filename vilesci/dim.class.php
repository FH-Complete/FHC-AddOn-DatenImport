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

class dim extends basis_db
{
	public $new=true;			//  boolean
	public $result = array();	//  adresse Objekt

	//Tabellenspalten
	public $dim_id;			//  integer
	public $diq_id;			//  (FK) integer
	public $diq_attribute;	//  varchar (512)
	public $fhc_attribute; 		//  text
	public $fhc_datatype; 		//  text
	public $updateamum;		//  timestamp
	public $updatevon;		//  string
	public $insertamum;     //  timestamp
	public $insertvon;      //  string
	
	public $dataobject=array();
	public $data=array();
	public $num_rows;     
	public $num_fields;   
	
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
	 * Laedt die Datenimportquelle mit der ID $di_id
	 * @param  $adress_id ID der zu ladenden Adresse
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($dim_id)
	{
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($dim_id) || $dim_id == '')
		{
			$this->errormsg = 'DI_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM addon.tbl_di_mapping WHERE dim_id=".$this->db_add_param($dim_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{	
			$this->dim_id			= $row->dim_id;
			$this->diq_id 			= $row->diq_id;
			$this->diq_attribute	= $row->diq_attribute;
			$this->fhc_attribute	= $row->fhc_attribute;
			$this->fhc_datatype		= $row->fhc_datatype;
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
	 * Laedt die Daten mit der ID $di_id
	 * @param  $adress_id ID der zu ladenden Adresse
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadData($qry)
	{
		// Daten leeren
		unset($this->data);
		$this->data=array();
		//Daten aus der Datenbank lesen
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage!';
			return false;
		}
		$this->num_rows=$this->db_num_rows();
		$this->num_fields=$this->db_num_fields();
		while($row = $this->db_fetch_assoc())
			$this->data[]=$row;
		return true;
	}
	
	/**
	 * Laedt alle Adressen zu der Person die uebergeben wird
	 * @param $pers_id ID der Person zu der die Adressen geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadDIQ($diq_id)
	{
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($diq_id) || $diq_id == '')
		{
			$this->errormsg = 'DIQ_id muss eine Zahl sein';
			return false;
		}
		
		//Lesen der Daten aus der Datenbank
		$qry = 'SELECT * FROM addon.tbl_di_mapping WHERE diq_id='.$diq_id.' ORDER BY dim_id;';
		//echo $qry;
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		$i=0;
		while($row = $this->db_fetch_object())
		{
			$obj = new dim();

			$obj->dim_id			= $row->dim_id;
			$obj->diq_id 			= $row->diq_id;
			$obj->diq_attribute		= $row->diq_attribute;
			$obj->fhc_attribute		= $row->fhc_attribute;
			$obj->fhc_datatype		= $row->fhc_datatype;
			//$obj->updateamum    = $row->updateamum;
			//$obj->updatevon     = $row->updatevon;
			//$obj->insertamum    = $row->insertamum;
			//$obj->insertvon     = $row->insertvon;
			$obj->new       = false;
			$this->result[] = $obj;

			// build dataobject
			$this->dataobject[$i]=(object) 'dm'; // Objekt deklarieren
			$this->dataobject[$i]->diq_schema=stristr($row->diq_attribute, '.', true); 			   			// string vor dem Punkt
			$this->dataobject[$i]->diq_table=substr(stristr($row->diq_attribute, '.'),1);		   			// string nach dem Punkt, erstes Zeichen verlieren
			$this->dataobject[$i]->diq_column=substr(stristr($this->dataobject[$i]->diq_table, '.'),1);	    // string nach dem Punkt, erstes Zeichen verlieren
			$this->dataobject[$i]->diq_table=stristr($this->dataobject[$i]->diq_table, '.', true); 			// string vor dem Punkt
			$this->dataobject[$i]->fhc_schema=stristr($row->fhc_attribute, '.', true); 			   			// string vor dem Punkt
			$this->dataobject[$i]->fhc_table=substr(stristr($row->fhc_attribute, '.'),1);		   			// string nach dem Punkt, erstes Zeichen verlieren
			$this->dataobject[$i]->fhc_column=substr(stristr($this->dataobject[$i]->fhc_table, '.'),1);	    // string nach dem Punkt, erstes Zeichen verlieren
			$this->dataobject[$i]->fhc_table=stristr($this->dataobject[$i]->fhc_table, '.', true); 			// string vor dem Punkt
			//$this->dataobject[$i]->fhc_datatype=$row->fhc_datatype; 			// Ziel-Datentyp
			$this->dataobject[$i]->fhc_datatype=$this->getDatatype($this->dataobject[$i]->fhc_schema,$this->dataobject[$i]->fhc_table,$this->dataobject[$i]->fhc_column);
			$this->dataobject[$i]->dim_id			= $row->dim_id;
			$this->dataobject[$i]->diq_id 			= $row->diq_id;
			$this->dataobject[$i]->diq_attribute	= $row->diq_attribute;
			$this->dataobject[$i]->fhc_attribute	= $row->fhc_attribute;
			
			$i++;
		}
		//var_dump($this->dataobject);
		return true;
	}
	
	/**
	 * Ermitteln des Datentyps eines Attributs
	 * @return datatype
	 */
	public function getDatatype($schema,$table,$column)
	{
		// Neues DB-Objekt
		$dbo=new basis_db();
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT data_type FROM information_schema.columns WHERE table_schema='$schema' AND table_name='$table' AND column_name='$column'";

		if(!$dbo->db_query($qry))
		{
			$dbo->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		if(!$row = $dbo->db_fetch_object())
			return false;
		else
			switch ($row->data_type)
			{
				case 'character varying':
					return 'varchar';
				default:
					return $row->data_type;
			}
	}
	
	/**
	 * sendet den ersten Tabellennamen der Quellen
	 * @return tablename
	 */
	public function getDiqTable()
	{
		return $this->dataobject[0]->diq_schema.'.'.$this->dataobject[0]->diq_table;
	}
	
	/**
	 * sendet den ersten Tabellennamen der Ziele
	 * @return tablename
	 */
	public function getFhcTable()
	{
		return $this->dataobject[0]->fhc_schema.'.'.$this->dataobject[0]->fhc_table;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO addon.tbl_di_mapping (diq_id, diq_attribute, fhc_attribute, fhc_datatype,
			      insertamum, insertvon) VALUES('.
			      $this->db_add_param($this->diq_id,FHC_INTEGER).', '.
			      $this->db_add_param($this->diq_attribute).', '.
			      $this->db_add_param($this->fhc_attribute).', '.
			      $this->db_add_param($this->fhc_datatype).', now(), '.
			      $this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob dim_id eine gueltige Zahl ist
			if(!is_numeric($this->dim_id))
			{
				$this->errormsg = 'dim_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE addon.tbl_di_mapping SET'.
				' diq_attribute='.$this->db_add_param($this->diq_attribute).', '.
				' fhc_attribute='.$this->db_add_param($this->fhc_attribute).', '.
				' fhc_datatype='.$this->db_add_param($this->fhc_datatype).', '.
				' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).
		      	' WHERE dim_id='.$this->db_add_param($this->dim_id, FHC_INTEGER, false).';';
		}
        //echo $qry;
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('addon.tbl_di_mapping_dim_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->dim_id = $row->id;
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
			$this->errormsg = 'Fehler beim Update des DIM-Datensatzes';
			return false;
		}
		return $this->dim_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $di_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($di_id)
	{
		//Pruefen ob adresse_id eine gueltige Zahl ist
		if(!is_numeric($di_id) || $di_id == '')
		{
			$this->errormsg = 'di_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM addon.tbl_di WHERE di_id=".$this->db_add_param($di_id, FHC_INTEGER, false).";";

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
}
?>
