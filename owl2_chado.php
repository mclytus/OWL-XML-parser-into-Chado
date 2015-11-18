<?php
/**
 *  @file
 *  @add file from header
 *  
*/

// setting up array function to extract data needed from the OWL header section. 

$sectionheader ["name"] = 'owl:Ontology';
$sectionheader ["attribute"] = array ($key = value);
$sectionheader ["children"] = array();
	$sectionheader ["children"][]['name'] = 'foaf:homepage', 'dc:title', 'dc:description', 
	$sectionheader ["children"][]['attribute'] = 'http://obo-relation.googlecode.com';
	$sectionheader ["children"][]['value'] = 
	
// load the XML file
$xml = new XMLReader();

// We are going to go through the owl header section to get through the tags(nodes) which are same as element in the XML reader.
$xml->open($sectionheader);
$total_nodes =0;
while ($sectionheader->read()){  
	$total_nodes++;
}
$xml->close();

// now parse the input data

$nodes_read = 0
$attributes =array();
$xml->open($sectionheader);
while (($sectionheader->read()){
	$nodes_read++;
	if ($sectionheader->nodeType == XMLReader::ELEMENT) {
		// <data> //get node details between tags
		if (strcmp($sectionheader->name, 'key')==0) {
		
		// get attributes types 
		
		}
	}
}
elseif ($sectionheader->nodeType ==XMLReader::END_ELEMENT) { // </node>
}

