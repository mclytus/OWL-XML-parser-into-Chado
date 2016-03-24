<?php
/**
 * @file
 * @add file from header
 */

require_once('OWLStanza.inc');

/**
 * Parses an OWL XML file and imports the CV terms into Chado
 *
 * @param $filename
 *   The full path to the OWL XML file.
 *
 * @return
 *   No return value.
 *
 * @throws Exception
 */
function tripal_cv_parse_owl($filename) {

  // Open the OWL file for parsing.
  $owl = new XMLReader();
  if (!$owl->open($filename)) {
    print "ERROR opening OWL file: '$filename'\n";
    exit();
  }

  // Get the RDF stanza. We pass FALSE as the second parameter to prevent
  // the object from reading the entire file into memory.
  $rdf = new OWLStanza($owl,FALSE);

  // Get the ontology stanza so we can add the database and controlled
  // vocabulary records.
  $ontology = new OWLStanza($owl);

  // Inser the database record into Chado using the owl:Ontology stanza.
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

  // Insert the controlled vocabluary record into Chado using the
  // owl:Ontology stanza.
  $title = $ontology->getChild('dc:title');
  $description = $ontology->getChild('dc:description');
  $cv_name = preg_replace("/[^\w]/", "_", strtolower($title->getValue()));
  $cv = tripal_insert_cv($cv_name, $description->getValue());

  // loop through each stanza, one at a time, and handle each one
  // based on the tag name.
  $stanza =  new OWLStanza($owl);
  while (!$stanza->isFinished()) {

    // Use the tag name to identify which function should be called.
    switch ($stanza->getTagName()) {
      case 'owl:AnnotationProperty':
        // tripal_owl_handle_annotation_property($stanza);
        break;
      case 'rdf:Description':
        // tripal_owl_handle_description($stanza);
        break;
      case 'owl:ObjectProperty':
        // tripal_owl_handle_object_property($stanza);
        break;
      case 'owl:Class':
        tripal_owl_handle_class($stanza, $ontology);
        break;
      case 'owl:Axiom':
        break;
      case 'owl:Restriction':
        break;
      default:
        throw new Exception("Unhandled stanza: " . $stanza->getTagName());
        exit;
        break;
    }

    // Get the next stanza in the OWL file.
    $stanza =  new OWLStanza($owl);
  }

  // Close the XMLReader $owl object.
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

  $db_name = '';
  $accession = '';
  $about = $stanza->getAttribute('rdf:about');
  if (preg_match('/.*\/(.+)_(.+)/', $about, $matches)) {
    $db_name = strtoupper($matches[1]);
    $accession = $matches[2];
  }
  //echo "$about\n";






}




