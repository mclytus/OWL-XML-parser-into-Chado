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

  // Holds an array of CV and DB records that have already been
  // inserted (reduces number of queires).
  $vocabs = array();

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

  // Insert the database record into Chado using the owl:Ontology stanza.
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
        tripal_owl_handle_class($stanza, $vocabs);
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
function tripal_owl_handle_class($stanza, $vocabs) {

  // Initialize the database and cv variables.
  $db_name = '';
  $accession = '';
  $db = null;
  $cv = null;

  // Get the DB name and accession from the about attribute.
  $about = $stanza->getAttribute('rdf:about');
  if (preg_match('/.*\/(.+)_(.+)/', $about, $matches)) {
     $db_name = strtoupper($matches[1]);
     $accession = $matches[2];
  }
  else {
    throw new Exception("owl:Class stanza is missing the 'rdf:about' attribute. " .
      "This is necessary to determine the term's accession: \n\n" . $stanza->getXML());
  }

  // Insert a DB record if it doesn't already exist.
  if (array_key_exists($db_name, $vocabs)) {
    $db = $vocabs[$db_name]['db'];
    $cv = $vocabs[$db_name]['cv'];
  }
  else {
    // Unfortunately, all we have is the name. The OWL format
    // doesn't provides us the URL, description, etc.
    $values = array(
      'name' => $db_name
    );
    $db = tripal_insert_db($values);

    // Check to see if this database has records and if so, what CV it is using.
    // Because the OWL Class doensn't specify a name that Chado wants for the
    // cv table, we have to discovery it or add it. If we find a single record
    // that has a cvterm (hence associated with a CV) then we'll reuse the same
    // CV. Otherwise, we must add a new CV record and we'll use the $db_name
    // as the name.
    $sql = "
      SELECT CV.*
      FROM {cvterm} CVT
          INNER JOIN {dbxref} DBX ON DBX.dbxref_id = CVT.dbxref_id
          INNER JOIN {db} DB      ON DB.db_id      = DBX.db_id
          INNER JOIN {cv} CV      ON CVT.cv_id     = CV.cv_id
        WHERE DB.db_id = :db_id
        LIMIT 1 OFFSET 0
      ";
    $results = chado_query($sql, array(':db_id' => $db->db_id));
    $cv = $results->fetchObject();
    // If there are no terms using this databaset then we need to add the
    // vocabulary.
    if (!$cv) {
      $cv = tripal_insert_cv($db->name, '');
    }

    // Add our new DB and CV to the vocab array.
    $vocabs[$db_name]['cv'] = $cv;
    $vocabs[$db_name]['db'] = $db;
  }

  // Insert a dbxref record.
  $values = array(
    'db_id' => $db->db_id,
    'accession' => $accession
  );
  $dbxref = tripal_insert_dbxref($values);

  // Insert a new cvterm record.
  $cvterm_name = '';
  $definition = '';

  $term = array(
  	'id' => $db->name .':'. $dbxref->accession,
  	'name' => $cvterm_name,
  	'cv_name' => $cv->name,
  	'definition' => $definition,
  );
  $option =array();
  if ($vocabs['this'] != $db->name){
  	$option['update_existing'] = FALSE;
  }
  $cvterm = tripal_insert_cvterm($term, $option);





}

