<?php
/**
 * @file
 * @add file from header
 */

require_once('OWLStanza.inc');


tripal_cv_parse_owl('ro.owl');

/**
 *
 * @param unknown $filename
 */

function tripal_cv_parse_owl($filename) {

  // Load the XML file.
  $owl = new XMLReader();

  // Open the OWL file for parsing.
  if (!$owl->open($filename)) {
    print "ERROR opening OWL file: '$filename'\n";
    exit();
  }

  $rdf = new OWLStanza($owl, FALSE);
  print_r ($rdf);

  $ontology = new OWLStanza($owl);
  print_r($ontology);

  while ($owl->nodeType == XMLReader::END_ELEMENT and $owl->name == 'rdf:RDF') {
  	$stanza =  new OWLStanza($owl);
  	switch ($stanza->getTagName()) {
  		case 'owl:AnnotationProperty':
  			tripal_owl_handle_annotation_property($stanza);
  			break;
  		case 'rdf:Description':
  			tripal_owl_handle_description($stanza);
  			break;
  		case 'owl:ObjectProperty':
  			tripal_owl_handle_object_property($stanza);
  			break;
  		case 'owl:Class':
  			tripal_owl_handle_class($stanza, $ontology);
  			break;
  		default:
  	}
  }

  $owl->close();
}


/**
 *
 * @param
 * $owl
 */
function tripal_owl_handle_object_property($stanza) {

}
/**
 *
 * @param
 * $owl
 */
function tripal_owl_handle_annotation_property($stanza) {

}

/**
 *
 * @param
 * $owl
 */
function tripal_owl_handle_description($stanza) {

}

/**
 *
 * @param
 * $owl
 */
function tripal_owl_handle_class($stanza, $ontology) {

}
