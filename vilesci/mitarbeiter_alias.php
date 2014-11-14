<?php
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/functions.inc.php');

$qry = "
SELECT 
	vorname, nachname, uid, alias
FROM 
	public.tbl_person 
	JOIN public.tbl_benutzer USING(person_id)
	JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
WHERE
	mitarbeiter_uid NOT in ('administrator','oesi','winter','_DummyLektor','sekretariat')";

$db = new basis_db();

$benutzer = new benutzer();

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		//Leerzeichen durch - ersetzten
		$row->vorname = str_replace(' ','-',$row->vorname);
		//Sonerzeichen filtern
		$alias = convertProblemChars($row->vorname.'.'.$row->nachname);
		//Doppelte Punkte entfernen
		$alias = str_replace('..','.',$alias);
		//alles klein schreiben
		$alias = mb_strtolower(mb_substr($alias, 0, 32));

		if($alias!=$row->alias)
		{
			$qry_update="UPDATE public.tbl_benutzer SET alias=".$db->db_add_param($alias)." WHERE uid=".$db->db_add_param($row->uid);

			if(!$benutzer->alias_exists($alias))
			{
				if($db->db_query($qry_update))
					echo '<br>Alias von '.$row->uid.' auf '.$alias.' gesetzt';
			}
			else
				echo "<br>Alias fuer $row->uid kann nicht auf $alias gesetzt werden";
		}
	}
}
?>
