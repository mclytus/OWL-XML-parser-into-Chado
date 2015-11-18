<?php
/**
 *  @file
 *  @add file from header
 *  
*/

// Example sections array. 
//
// $sectionheader ["name"] = 'owl:Ontology';
// $sectionheader ["attribute"] = array (
//   'rdf:about' => 'http://purl.obolibrary.org/obo.ro.owl',
//   'xyz:blah' => 'uinfo'
// );
// $sectionheader ["attribute"] = array();
// $sectionheader ["attributes"]['rdf:about'] = 'http://purl.obolibrary.org/obo.ro.owl';
// $sectionheader ["attributes"]['xyz:blah'] = 'uinfo';
// $sectionheader ["children"] = array();
//   $sectionheader ["children"][]['name'] = 'foaf:homepage'; 
//   $sectionheader ["children"][]['attributes'] = array('rdf:dataype' => 'http://www.w3.org/2001/XMLSchema#anyURI');
//   $sectionheader ["children"][]['value'] = 'http://obo-relation.googlecode.com'
//
//   $sectionheader ["children"][]['name'] = 'dc:title'; 
//   $sectionheader ["children"][]['attributes'] = array('xml:lang' => 'en') 
//   $sectionheader ["children"][]['value'] = 'The OBO Relations Onot.....'
  
// Load the XML file.
$owl = new XMLReader();

// Open the OWL file for parsing.
$owl->open('ro.owl');
while ($owl->read()){  
	if ($owl->nodeType == XMLReader::ELEMENT) {
      // Build the section array.
	  $section = array();
	  $section['name'] = $owl->name;
	  
	  // First, get the attributes.
	  // Second, get the value.
	  // Thrid, get the children.
	  getSectionChildren($owl, $section);
	  
	  // Deal with each section of OWL.
	  switch ($section['name']) {
	  	case 'owl:AnnotationProperty':
	  		print $section['name'] . "\n";
	  		handleAnnotationProperty($section);
	  		break;
	  	default:
	  		
	  }
	  
	}
}

/**
 * 
 */
function getSectionChildren($owl, &$section) {
	while ($owl->read()){
		if ($owl->nodeType == XMLReader::ELEMENT) {
		
		}
		if ($owl->name == $section['name'] && $owl->nodeType == XMLReader::END_ELEMENT) {
		  return;
		}
	}
}
/**
 * 
 */
function handleAnnotationProperty(&$section) {
	
}
// now parse the input data

/* $nodes_read = 0
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

$xml->close(); */

