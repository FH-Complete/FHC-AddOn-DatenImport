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
	JOIN public.tbl_prestudent ON(tbl_prestudent.person_id=tbl_benutzer.person_id)";

$db = new basis_db();

$benutzer = new benutzer();

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$row->vorname = str_replace(' ','-',$row->vorname);
		$row->vorname = str_replace('’','',$row->vorname);
		$row->nachname = str_replace('’','',$row->nachname);

		$alias = convertProblemChars($row->vorname.'.'.$row->nachname);
		//Doppelte Punkte entfernen
		$alias = str_replace('..','.',$alias);
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
