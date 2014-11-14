<?php
/*
 * test.php
 * 
 * Copyright 2013 Christian Paminger <pam@nb-pam-X230>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>unbenannt</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Geany 1.23.1" />
</head>

<body>
	<?php
		/*$xml = new XMLReader();
		$xml->open('../xml/bismeldung_WS2013_Stg257.xml');
		echo $xml->read();
		if (!$xml->moveToAttribute('StgKz'))
			die('Fehler');
		echo $xml->readString();
		//echo $xml->readInnerXML();*/
		
		/*$document = simplexml_load_file('../xml/bismeldung_WS2013_Stg257.xml');
		//var_dump($document);
		//echo $document->asXML();
		$xml = new SimpleXMLElement($document->asXML());
		//var_dump($xml);
		// Search for <a><b><c> 
		$result = $xml->xpath('StudierendenBewerberMeldung/Studiengang/StudentIn');
		var_dump($result);
		while(list( , $node) = each($result)) 
		{
			echo '/a/b/c: ',$node,"\n";
		}*/
		
		
		$xslDoc = new DOMDocument();
		$xslDoc->load("../data/xml/BIS60-StudierendenmeldungStudierende.xslt");

		$xmlDoc = new DOMDocument();
		$xmlDoc->load("../data/xml/bismeldung_WS2013_Stg257.xml");

		$proc = new XSLTProcessor();
		$proc->importStylesheet($xslDoc);
		echo $proc->transformToXML($xmlDoc);
		
	?>
</body>

</html>
