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

  $rdf = new OWLStanza($owl);
  //print_r ($rdf);

  $ontology = NULL;
  foreach ($rdf->getChildren() as $child)
  {
  	if ($child->getTagName() == 'owl:Ontology')
  	{
  		$ontology = $child;
  		break;
  	}
  }
  //$ontology = new OWLStanza($owl);
  //print_r($ontology);


  $about = $ontology->getAttribute('rdf:about');
  if (preg_match('/^.*\/(.*)\.owl.*$/', $about, $matches)) {
  	$db_name = strtoupper($matches[1]);
  }
  // print_r("This should be out of the Ontology: ". $db_name. '\n');

  $ontologies = array();
$homepage = '';

foreach ($ontology->getChildren() as $child)
	{
	if ($child->getTagName() == 'foaf:homepage') {
	$homepage = $child->getAttribute('rdf:datatype');

	}
	}



  echo $homepage . " -> " . $db_name . "\n";


  $db = array(
  		'url' => $homepage,
  		'name' => $db_name
  );
	// $db = tripal_insert_db($db);
	$title = '';
	foreach ($ontology->getChildren() as $child)
	{
		if ($child->getTagName() == 'dc:title') {
			$title = $child->getValue();
		}

	}
	echo $title . "\n";


	$cv_name = strtolower($title);
foreach ($ontology->getChildren() as $child)
{
	if ($child->getTagName() == 'dc:description') {
		$description = $child->getValue();
	}
}
	echo $description . "\n";

	// $cv = tripal_insert_cv($cv_name, $description);

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

	if ($this->owl->nodeType == XMLReader::ELEMENT and $this->owl->name == 'owl:AnnotationProperty') {

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

	$about = $stanza->getAttribute('rdf:about');
	print "$about\n";

	$matches = array();
	$db_name = '';
	$accession = '';
	if (preg_match('/.*\/(.+)_(.+)/', $about, $matches)) {
		$db_name = strtoupper($matches[1]);
		$accession = $matches[2];
	}

}



}