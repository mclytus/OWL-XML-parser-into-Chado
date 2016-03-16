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
  
//   if ($owl->nodeType == XMLReader::END_ELEMENT and $owl->name == 'rdf:RDF') {
//   	$stanza =  new OWLStanza($owl);
//   	switch ($stanza->getTagName()) {
//   		case 'owl:AnnotationProperty':
//   			tripal_owl_handle_annotation_property($stanza);
//   			break;
//   		case 'rdf:Description':
//   			tripal_owl_handle_description($stanza);
//   			break;
//   		case 'owl:ObjectProperty':
//   			tripal_owl_handle_object_property($stanza);
//   			break;
//   		case 'owl:Class':
//   			tripal_owl_handle_class($stanza, $ontology);
//   			break;
//   		default:
//   	}
//   }
    
  return;

  // Holds all of the namespaces used by this OWL file.
  $namespaces = array();

  // Holds an array of CV and DB records that have already been
  // inserted (reduces number of queires).
  $ontologies = array();

  do {
    if ($owl->nodeType == XMLReader::ELEMENT) {
      // Deal with each section of OWL.
      switch ($owl->name) {
        case 'rdf:RDF':
          tripal_owl_handle_namespaces($owl, $namespaces);
          // print_r($namespaces);
          break;
        case 'owl:Ontology':
          tripal_owl_handle_header($owl, $ontologies);
          break;
        case 'owl:AnnotationProperty':
        tripal_owl_handle_annotation_property($owl);
        break;
        case 'rdf:Description':
        tripal_owl_handle_description($owl);
        break;
        case 'owl:ObjectProperty':
        tripal_owl_handle_object_property($owl);
        break;
        case 'owl:Class':
          tripal_owl_handle_class($owl, $ontologies);
          break;
        default:
          // print "Unhandled stanza: " . $owl->name . "\n";
          $owl->next();
      }
    }
  }
  while ($owl->read());

  // Close the XMLReader file object.
  $owl->close();
}

/**
 * Retreives the namespaces from the OWL rdf:RDF tag.
 *
 * @param $owl 
 * The XML reader object.
 * @param $namespaces
 * An empty array into which the namespaces will be written
 *
 * @return no return value.
 */
function tripal_owl_handle_namespaces($owl, &$namespaces) {
  $num_attrs = $owl->attributeCount;
  $owl->moveToFirstAttribute();
  for ($i = 0; $i < $num_attrs; $i++) {
    $owl->moveToNextAttribute();
    $matches = array();
    if (preg_match('/^xmlns:(.*)$/', $owl->name, $matches)) {
      $namespaces[$matches[1]] = $owl->value;
    }
  }
}

/**
 *
 * @param
 * $owl
 */
function tripal_owl_handle_header($owl, &$ontologies) {
  $title = '';
  $description = '';
  $homepage = '';
  $db_name = '';
  $matches = array();

  // The about attribute contains the URL for the resource of this
  // OWL file. We will use the name of the OWL file as the
  // chado.db.name field.
  $about = $owl->getAttribute('rdf:about');
  if (preg_match('/^.*\/(.*)\.owl.*$/', $about, $matches)) {
    $db_name = strtoupper($matches[1]);
  }
  else {
    print "ERROR: count not find the database name\n";
    exit();
  }

  // Itereate through the XML elements to find the header tags that we
  // will support.
  while ($owl->read()) {
    if ($owl->nodeType == XMLReader::ELEMENT) {
      $matches = array();
      $name = $owl->name;
      switch ($name) {
        case 'dc:title':
          $owl->read();
          $title = $owl->value;
          // Goes to the chado.cv.name field.
          break;
        case 'dc:description':
          $owl->read();
          $description = $owl->value;
          // Goes to the chado.cv.description field.
          break;
        case 'dc:source':
          $owl->read();
          $resource = $owl->value;
          break;
        case 'foaf:homepage':
          $owl->read();
          $homepage = $owl->value;
          // Goes to the chado.db.url field.
          break;
        case 'owl:imports':
          // Ignore these lines. Not needed for loading into Chado.
          break;
      }
    }
    // If we've reached the end of the Header block then return.
    if ($owl->nodeType == XMLReader::END_ELEMENT and $owl->name == 'owl:Ontology') {
      // print "DB Name: $db_name\n";
      // print "Title: $title\n";
      // print "Description: $description\n";
      // print "Homepage: $homepage\n";
      // print "Resource: $resource\n";

      // Create the CV and DB records here.
      $cv_name = preg_replace("/[^\w]/", "_", strtolower($title));
      $cv = tripal_insert_cv($cv_name, $description);
      if (!$cv) {
        print "ERROR: '\n";
        exit();
      }

      $db = array(
        'url' => $homepage,
        'name' => $db_name
      );
      $db = tripal_insert_db($db);
      if (!$db) {
        print "ERROR: '\n";
        exit();
      }

      $ontologies[$db_name]['cv'] = $cv;
      $ontologies[$db_name]['db'] = $db;
      $ontologies['this'] = $db_name;
      return;
    }
  }
}

/**
 *
 * @param
 * $owl
 */
function tripal_owl_handle_object_property($owl) {
  // The about attribute contains the URL for the resource (our term).
  // The chado.db.name and chado.dbxref.accession should be in this URL
  // and we can
  $about = $owl->getAttribute('rdf:about');
  // print "$about\n";

  // If this element is empty then just return.
  if ($owl->isEmptyElement) {
    return;
  }

  // Move the element pointer to the first element inside of the
  // ObjectProperty stanza.
  $owl->read();

  // Itereate through the XML elements to find the header tags that we
  // will support.
  do {
    // If we've reached the end of the ObjectProperty block then rreturn.
    if ($owl->nodeType == XMLReader::END_ELEMENT and $owl->name == 'owl:ObjectProperty') {
      return;
    }
    else if ($owl->nodeType == XMLReader::ELEMENT) {
      $matches = array();
      $name = $owl->name;
      switch ($name) {
        case 'rdfs:label':
          $owl->read();
          $value = $owl->value;
          // print "$name\t$value\n";
          // Goes to chado.cvterm.name
          break;
        case 'rdfs:range':
          // Not needed for Chado.
          break;
        case 'rdfs:domain':
          break;
        case 'rdfs:subPropertyOf':
          break;
        case 'rdfs:comment':
          break;
        case 'rdf:type':
          break;
        case (preg_match('/^oboInOwl:(.*)$/', $name, $matches) ? TRUE : FALSE):
          // create a function to read in the OboInOwl tags.
          $accession = $matches[1];
          break;
        case (preg_match('/^obo:(.*)$/', $name, $matches) ? TRUE : FALSE):
          // OBO terms can either be from the OBO vocabulary or from another
          // onotology (e.g. IAO or RO).
          $id = $matches[1];
          $vocab = '';
          $accession = '';
          $matches = array();
          if (preg_match('/^(.*)_(.*)$/', $id, $matches)) {
            // $vocab = $matches[1];
            // $owl->read();
            // $value = $owl->value;
            // print "$name\t$value\n";
            // $accession = $matches[2];
          }
          else {
            $vocab = 'OBO';
            $accession = $id;
          }
          // Deal with the term
          break;
        case (preg_match('/^owl:(.*)$/', $name, $matches) ? TRUE : FALSE):
          break;
      }
    }
  }
  while ($owl->next());
}
/**
 *
 * @param
 * $owl
 */
function tripal_owl_handle_annotation_property($owl) {
  // The about attribute contains the URL for the resource (our term).
  // The chado.db.name and chado.dbxref.accession should be in this URL
  // and we can
  $about = $owl->getAttribute('rdf:about');
  // print "$about\n";

  // If this element is empty then just return.
  if ($owl->isEmptyElement) {
    return;
  }

  // Move the element pointer to the first element inside of the
  // AnnotationProperty stanza.
  $owl->read();

  // Itereate through the XML elements to find the header tags that we
  // will support.
  do {
    // If we've reached the end of the Annotation Property block then return.
    if ($owl->nodeType == XMLReader::END_ELEMENT and $owl->name == 'owl:AnnotationProperty') {
      return;
    }
    else if ($owl->nodeType == XMLReader::ELEMENT) {
      $matches = array();

      $name = $owl->name;
      switch ($name) {
        case 'rdfs:label':
          $owl->read();
          $value = $owl->value;
          // print "$name\t$value\n";

          // Goes to chado.cvterm.name
          break;
        case 'rdfs:seeAlso':
          // Not needed for Chado.
          break;
        case 'foaf:page':
          break;
        case 'rdfs:subPropertyOf':
          if ($owl->isEmptyElement) {
            $resource = $owl->getAttribute('rdf:resource');
            // print $resource . "\n";
          }
          break;
        case 'rdfs:comment':
          $owl->read();
          $value = $owl->value;
          // print "$name\t$value\n";
          break;
        // case (preg_match('/^oboInOwl:(.*)$/', $name, $matches) ? TRUE : FALSE):
        // create a function to read in the OboInOwl tags.
        // $accession = $matches[1];
        // break;
        case (preg_match('/^obo:(.*)$/', $name, $matches) ? TRUE : FALSE):
          // OBO terms can either be from the OBO vocabulary or from another
          // onotology (e.g. IAO or RO).
          $id = $matches[1];
          $vocab = '';
          $accession = '';
          $matches = array();
          if (preg_match('/^(.*)_(.*)$/', $id, $matches)) {
            $vocab = $matches[1];
            $owl->read();
            $value = $owl->value;
            // print "$name\t$value\n";
            $accession = $matches[2];
          }
          else {
            $vocab = 'OBO';
            $accession = $id;
          }
          // Deal with the term
          break;
        case (preg_match('/^owl:(.*)$/', $name, $matches) ? TRUE : FALSE):
          break;
      }
    }
  }
  while ($owl->next());
}

/**
 *
 * @param
 * $owl
 */
function tripal_owl_handle_description($owl) {
  // The about attribute contains the URL for the resource (our term).
  // The chado.db.name and chado.dbxref.accession should be in this URL
  // and we can
  $about = $owl->getAttribute('rdf:about');
  // print "$about\n";

  // If this element is empty then just return.
  if ($owl->isEmptyElement) {
    return;
  }

  // Move the element pointer to the first element inside of the
  // Description stanza.
  $owl->read();

  do {
    // If we've reached the end of the description block then return.
    if ($owl->nodeType == XMLReader::END_ELEMENT and $owl->name == 'rdf:Description') {
      return;
    }
    else if ($owl->nodeType == XMLReader::ELEMENT) {
      $matches = array();
      $name = $owl->name;
      switch ($name) {
        case 'rdfs:label':
          $owl->read();
          $value = $owl->value;
          print "$name\t$value\n";

          // Goes to chado.cvterm.name
          break;
        case 'rdfs:subPropertyOf':
          if ($owl->isEmptyElement) {
            $resource = $owl->getAttribute('rdf:resource');
            // print $resource . "\n";
          }
          break;
        case 'rdfs:subClassOf':
          break;

        case (preg_match('/^oboInOwl:(.*)$/', $name, $matches) ? TRUE : FALSE):
          // create a function to read in the OboInOwl tags.
          $accession = $matches[1];
          break;
        case (preg_match('/^obo:(.*)$/', $name, $matches) ? TRUE : FALSE):
          // OBO terms can either be from the OBO vocabulary or from another
          // onotology (e.g. IAO or RO).
          $id = $matches[1];
          $vocab = '';
          $accession = '';
          $matches = array();
          if (preg_match('/^(.*)_(.*)$/', $id, $matches)) {
            $vocab = $matches[1];
            $owl->read();
            $value = $owl->value;
            // print "$name\t$value\n";
            $accession = $matches[2];
          }
          else {
            $vocab = 'OBO';
            $accession = $id;
          }
          // Deal with the term
          break;
        case (preg_match('/^owl:(.*)$/', $name, $matches) ? TRUE : FALSE):
          break;
      }
    }
  }
  while ($owl->next());
}

/**
 *
 * @param
 * $owl
 */
function tripal_owl_handle_class($owl, &$ontologies) {
  // The about attribute contains the URL for the resource (our term).
  // The chado.db.name and chado.dbxref.accession should be in this URL
  // and we can
  $about = $owl->getAttribute('rdf:about');
  // print "$about\n";

  // Get the DB name and accession from the about attribute.
  $matches = array();
  $db_name = '';
  $accession = '';
  if (preg_match('/.*\/(.+)_(.+)/', $about, $matches)) {
    $db_name = strtoupper($matches[1]);
    $accession = $matches[2];
  }

  // Insert a DB record.
  $db = null;
  if (!array_key_exists($db_name, $ontologies)) {
    $db = array(
      'name' => $db_name
    );
    $db = tripal_insert_db($db);
    if (!$db) {
      print "ERROR: '\n";
      exit();
    }
    $ontologies[$db_name]['db'] = $db;
    $ontologies[$db_name]['cv'] = NULL;
  }
  else {
    $db = $ontologies[$db_name]['db'];
  }

  // Insert a dbxref record.
  $values = array(
    'db_id' => $db->db_id,
    'accession' => $accession
  );
  $dbxref = tripal_insert_dbxref($values);
  if (!$dbxref) {
    print "ERROR: '\n";
    exit();
  }

  // Check to see if this database has records and if so, what CV it is using.
  // Because the OWL Class doensn't specify a name that Chado wants for the
  // cv table, we have to discovery it or add it. If we find a single record
  // that has a cvterm (hence associated with a CV) then we'll reuse the same
  // CV. Otherwise, we must add a new CV record and we'll use the $db_name
  // as the name.
  $cv = null;
  if (!$ontologies[$db_name]['cv']) {
    $sql = "
      SELECT CV.*
      FROM {cvterm} CVT
        INNER JOIN {dbxref} DBX ON DBX.dbxref_id = CVT.dbxref_id
        INNER JOIN {db} DB      ON DB.db_id      = DBX.db_id
        INNER JOIN {cv} CV      ON CVT.cv_id     = CV.cv_id
      WHERE DB.db_id = :db_id
      LIMIT 1 OFFSET 0
    ";
    $results = chado_query($sql, array(
      ':db_id' => $db->db_id
    ));
    if (!$results) {
      $cv = tripal_insert_cv($db->name, '');
    }
    else {
      $cv = $results->fetchObject();
    }
    $ontologies[$db_name]['cv'] = $cv;
  }
  else {
    $cv = $ontologies[$db_name]['cv'];
  }

  // Inserting CV term record. IMPORTANT NOTE: The insert cvterm function need to be
  // fixed to add the db_id to be incorporated into the function.

  // If this element is empty then just return.
  if ($owl->isEmptyElement) {
    return;
  }

  // Move the element pointer to the first element inside of the
  // ObjectProperty stanza.
  $owl->read();
  $cvterm_name = null;
  $definition = '';
  do {
    // If we've reached the end of the Class block then return.
    if ($owl->nodeType == XMLReader::END_ELEMENT and $owl->name == 'owl:Class') {
      // Create the term array used for teh tripal_insert_cvterm() function.
      $term = array(
        'id' => $db->name .':'. $dbxref->accession,
        'name' => $cvterm_name,
        'cv_name' => $cv->name,
        'definition' => $definition,
      );

      // It is possible to have terms from other vocabularies in the
      // the OWL file.  We want to add these terms if they do not exists
      // because they will have relatioships to terms in this vocabulary.
      // But, we do not want to overwrite the terms if they belong to
      // another vocabulary and they already exists.  So, we will
      // set the $options array not update if the database for the
      // terms is different from the database for this file.
      $option =array();
      if ($ontologies['this'] != $db->name){
        $option['update_existing'] = FALSE;
      }

      // Insert the term and return;
      $cvterm = tripal_insert_cvterm($term, $option);
      return;
    }
    else if ($owl->nodeType == XMLReader::ELEMENT) {
      $matches = array();
      $name = $owl->name;
      switch ($name) {
        case 'rdfs:label':
          $owl->read();
          $cvterm_name = $owl->value;
          // print "$name\t$value\n";

          // Goes to chado.cvterm.name
          break;
        case 'rdfs:subClassOf':
          if ($owl->isEmptyElement) {
            $resource = $owl->getAttribute('rdf:resource');
            // print $resource . "\n";
          }
          else {
          }
          break;
        case (preg_match('/^obo:(.*)$/', $name, $matches) ? TRUE : FALSE):
          // OBO terms can either be from the OBO vocabulary or from another
          // onotology (e.g. IAO or RO).
          $id = $matches[1];
          $vocab = '';
          $accession = '';
          $matches = array();
          if (preg_match('/^(.*)_(.*)$/', $id, $matches)) {
            $vocab = $matches[1];
            $owl->read();
            $value = $owl->value;
            // print "$name\t$value\n";
            $accession = $matches[2];

          }
          else {
            $vocab = 'OBO';
            $accession = $id;
          }
          // Deal with the term
          break;
        case (preg_match('/^owl:(.*)$/', $name, $matches) ? TRUE : FALSE):
          break;
      }
    }
  }
  while ($owl->next());
}
