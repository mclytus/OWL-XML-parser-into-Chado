<?php
/**
 *  @file
 *  @add file from header
 *  
*/

$value = //URI rdf:about = ""

$sectionheader ["name"] = 'owl:Ontology';
$sectionheader ["attribute"] = array ($key-> value);
$sectionheader ["children"] = array();
	$sectionheader ["children"][]['name'] = 'foaf:homepage';
	$sectionheader ["children"][]['attribute'] = 'http://obo-relation.googlecode.com';
	$sectionheader ["children"][]['value'] = 
	
	
// load the XML file
$owl = new XMLReader();

// We are going to go through the owl header section to get through the tags.
$owl->open($sectionheader);
$total_nodes =0;
while ($sectionheader->read()){  
	$total_nodes++;
}
$owl->close();
