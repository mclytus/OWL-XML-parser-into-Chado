<?php
/**
 * @file
 * @add file from header
 */
require_once ('OWLStanza.inc');

/**
 * Parses an OWL XML file and imports the CV terms into Chado
 *
 * @param $filename The
 *          full path to the OWL XML file.
 *
 * @return No return value.
 *
 * @throws Exception
 */
function tripal_cv_parse_owl($filename) {

  // TODO: this all should occur inside of a transaction.

  // Open the OWL file for parsing.
  $owl = new XMLReader();
  if (!$owl->open($filename)) {
    print "ERROR opening OWL file: '$filename'\n";
    exit();
  }

  // Get the RDF stanza. We pass FALSE as the second parameter to prevent
  // the object from reading the entire file into memory.
  $rdf = new OWLStanza($owl, FALSE);

  // Get the ontology stanza. It will contain the values for the database
  // name for this ontology.
  $ontology = new OWLStanza($owl);

  // Insert the database record into Chado using the owl:Ontology stanza.
  $about = $ontology->getAttribute('rdf:about');
  if (preg_match('/^.*\/(.*)\.owl.*$/', $about, $matches)) {
    $db_name = strtoupper($matches[1]);
  }

  //
  // Step 1: Make sure that all dependencies are met
  //

  // loop through each stanza, one at a time, and handle each one
  // based on the tag name.
  $stanza = new OWLStanza($owl);
  $deps = array ();

  while (!$stanza->isFinished()) {
    // Use the tag name to identify which function should be called.
    switch ($stanza->getTagName()) {
      case 'owl:Class':
        tripal_owl_check_class_depedencies($stanza, $db_name, $deps);
        break;
    }
    // Get the next stanza in the OWL file.
    $stanza = new OWLStanza($owl);
  }
  if (count(array_keys($deps)) > 0) {
    // We have unmet dependencies. Print those out and return.
    // print('We have missing dependencies vocabularies (db_names) that are not in the Chado database.
    // The deps array will have DB’s, then terms' . "\n");
    print_r($deps);
    return;
  }

  //
  // Step 2: If we pass the dependency check in step 1 then we can insert
  // the terms.
  //

  // Holds an array of CV and DB records that have already been
  // inserted (reduces number of queires).
  $vocabs = array ();

  // Reload the ontology to reposition at the beginning for inserting the
  // new terms.

  $owl = new XMLReader();
  $rdf = new OWLStanza($owl, FALSE);
  $ontology = new OWLStanza($owl);

  // Insert the database record into Chado using the
  // owl:Ontology stanza.
  $homepage = $ontology->getChild('foaf:homepage');

  $db = array (
    'url' => $homepage->getValue(),
    'name' => $db_name
  );
  $db = tripal_insert_db($db);

  // Insert the controlled vocabulary record into Chado using the
  // owl:Ontology stanza.
  $title = $ontology->getChild('dc:title');
  $description = $ontology->getChild('dc:description');
  $cv_name = preg_replace("/[^\w]/", "_", strtolower($title->getValue()));
  $cv = tripal_insert_cv($cv_name, $description->getValue());

  // Add this CV and DB to our vocabs array so we can reuse it later.
  $vocabs[$db_name]['cv'] = $cv;
  $vocabs[$db_name]['db'] = $db;
  $vocabs['this'] = $db_name;

  // loop through each stanza, one at a time, and handle each one
  // based on the tag name.
  $stanza = new OWLStanza($owl);
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
        tripal_owl_handle_class($stanza, $vocabs);
        break;
      case 'owl:Axiom':
        break;
      case 'owl:Restriction':
        break;
      default:
        throw new Exception("Unhandled stanza: " . $stanza->getTagName());
        exit();
        break;
    }

    // Get the next stanza in the OWL file.
    $stanza = new OWLStanza($owl);
  }

  // Close the XMLReader $owl object.
  $owl->close();
}

/**
 * Checks for required vocabularies that are not loaded into Chado.
 *
 * Some vocabularies use terms from other ontologies. If this is happens
 * we need to ensure that the dependent vocabularies are present in the
 * database prior to loading this one. This function adds to the $deps
 * array all of the database names and term accessions that are missing in
 * Chado.
 *
 * @param $stanza The
 *          OWLStanza object for the current stanza from the OWL file.
 * @param $vocab_db_name The
 *          name of the database for the vocabulary being loded.
 * @param $deps The
 *          dependencies array. The missing databases are provided in array
 *          using a 'db' key, and missing terms are in a second array using a
 *          'dbxref' key.
 */
function tripal_owl_check_class_depedencies(OWLStanza $stanza, $vocab_db_name, &$deps) {

  // Initialize the variables.
  $db_name = '';
  $accession = '';
  $db = null;

  // Get the DB name and accession from the "about" attribute.
  $about = $stanza->getAttribute('rdf:about');
  if (preg_match('/.*\/(.+)_(.+)/', $about, $matches)) {
    $db_name = strtoupper($matches[1]);
    $accession = $matches[2];
  }
  else {
    throw new Exception("owl:Class stanza is missing the
     'rdf:about' attribute. " . "This is necessary to determine the term's accession: \n\n" . $stanza->getXML());
  }

  // If the database name for this term is the same as the vocabulary
  // we are trying to load, then don't include it in the $deps array.
  if ($db_name == $vocab_db_name) {
    return;
  }

  // Check if the db_name does not exists in the chado.db table. If it
  // does not exist then add it to our $deps array. If the query fails then
  // throw an exception.
  $db = chado_select_record('db', array (
    'db_id'
  ), array (
    'name' => $db_name
  ));
  if ($db === FALSE) {
    throw new Exception("Failed to execute query to find vocabulary in chado.db table\n\n" . $stanza->getXML());
  }
  else if (count($db) == 0) {
    $deps['db'][$db_name] = TRUE;
    // Does this stanza provide the URL for the OWL file of this missing
    // dependency. If so then add it to our deps array.
    $imported_from = $stanza->getChild('obo:IAO_0000412');

    if ($imported_from == NULL) {
      return;
    }
    $url = $imported_from->getAttribute('rdf:resource');
    if ($url) {
      $deps['db'][$db_name] = $url;
    }
    return;
  }

  // If the db_name exists, then check if the accession exists in
  // the chado.dbxref table. If it doesn't exist then add an entry to the
  // $deps array. If the query fails then throw an exception.
  $values = array (
    'db_id' => $db[0]->db_id,
    'accession' => $accession
  );

  $dbxref = chado_select_record('dbxref', array (
    'dbxref_id',
    'db_id'
  ), $values);
  if ($dbxref === FALSE) {
    throw new Exception("Failed to execute query to find vocabulary term in chado.dbxref table\n\n" . $stanza->getXML());
  }
  elseif (count($accession) == 0) {
    $deps['dbxref'][$db_name . ':' . $accesson] = TRUE;
  }
  return;
}

/**
 *
 * @param
 *          $stanza
 * @param
 *          $vocabs
 * @throws Exception
 */
function tripal_owl_handle_object_property($stanza) {
}

/**
 *
 * @param
 *          $stanza
 * @param
 *          $vocabs
 * @throws Exception
 */
function tripal_owl_handle_annotation_property($stanza) {
}

/**
 *
 * @param
 *          $stanza
 * @param
 *          $vocabs
 * @throws Exception
 */
function tripal_owl_handle_description($stanza) {
}

/**
 *
 * The function goes through owl:Class stanza to insert new vocabularies.
 *
 * @param $stanza The
 *          OWLStanza object for the current stanza from the OWL file.
 * @param
 *          $vocabs
 *
 * @throws Exception
 */
function tripal_owl_handle_class(OWLStanza $stanza, $vocabs) {

  // Initialize the database and cv variables.
  $db_name = '';
  $accession = '';
  $is_a = '';
  $db = null;
  $cv = null;

  // Get the DB name and accession from the about attribute.
  $about = $stanza->getAttribute('rdf:about');
  if (preg_match('/.*\/(.+)_(.+)/', $about, $matches)) {
    $db_name = strtoupper($matches[1]);
    $accession = $matches[2];
    $db = $vocabs[$db_name]['db'];
    $cv = $vocabs[$db_name]['cv'];
  }
  else {
    throw new Exception("owl:Class stanza is missing the 'rdf:about' attribute. " . "This is necessary to determine the term's accession: \n\n" . $stanza->getXML());
  }

  // Insert a dbxref record.
  if ($db_name == $vocabs['this']) {
    $values = array (
      'db_id' => $db->db_id,
      'accession' => $accession
    );
    $dbxref = tripal_insert_dbxref($values);
  }

  // Insert a new cvterm record.
  $cvterm_name = '';
  $definition = '';
  $term = array (
    'id' => $db->name . ':' . $dbxref->accession,
    'name' => $cvterm_name,
    'cv_name' => $cv->name,
    'definition' => $definition
  );
  $option = array ();
  if ($vocabs['this'] != $db->name) {
    $option['update_existing'] = FALSE;
  }
  $cvterm = tripal_insert_cvterm($term, $option);

  // Add a record to the chado relationship table if an ‘rdfs:subClassOf’ child exists.
  // $cvterm_name = $stanza->getChild('rdfs:label');
  // $definition = $stanza->getChild('obo:IAO_0000115');
  // $term = array (
  // 'id' => $db->name . ':' . $dbxref->accession,
  // 'name' => $cvterm_name,
  // 'cv_name' => $cv->name,
  // 'definition' => $definition
  // );
  // $option = array ();
  // if ($vocabs['this'] != $db->name) {
  // $option['update_existing'] = FALSE;
  // }
}