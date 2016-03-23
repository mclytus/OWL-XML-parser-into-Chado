<?php
/**
 * @file
 * @add file from header
 */

require_once('OWLStanza.inc');

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

  $ontology = new OWLStanza($owl);

  $about = $ontology->getAttribute('rdf:about');
  if (preg_match('/^.*\/(.*)\.owl.*$/', $about, $matches)) {
    $db_name = strtoupper($matches[1]);
  }
  $homepage = $ontology->getChild('foaf:homepage');

  $db = array(
    'url' => $homepage->getValue(),
    'name' => $db_name
  );
  $db = tripal_insert_db($db);

  $title = $ontology->getChild('dc:title');
  $description = $ontology->getChild('dc:description');
  $cv_name = preg_replace("/[^\w]/", "_", strtolower($title->getValue()));
  $cv = tripal_insert_cv($cv_name, $description->getValue());



exit;

  foreach ($rdf->getChildren() as $stanza) {

    	switch ($stanza->getTagName()) {
  		case 'owl:AnnotationProperty':
  			// tripal_owl_handle_annotation_property($stanza);
  			//print_r("Unhandled stanza: " . $stanza->getTagName() . "\n");
  			break;
  		case 'rdf:Description':
  			// tripal_owl_handle_description($stanza);
  			break;
  		case 'owl:ObjectProperty':
  			// tripal_owl_handle_object_property($stanza);
  			break;
  		case 'owl:Class':
  			tripal_owl_handle_class($stanza, $ontology);
  			echo $stanza;
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




