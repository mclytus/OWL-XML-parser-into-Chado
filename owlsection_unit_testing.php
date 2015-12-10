<?php
/**
 * @files
 * @add file from OWL OBO header
 */

// Loading the XML file
$owl = new XMLReader();
// Open the OWL file for parsing.
$owl->open('ro.owl');
$section = array();

$num_ops = 0;
// Loop throug the entire OWL file
$owl->read(); // reads rdf:RDF
$owl->read(); // reads owl::Ontology

// iterate through the next elements 
do {
  if ($owl->nodeType == XMLReader::ELEMENT and $owl->name == "rdf:Description") {
    $about = $owl->getAttribute('rdf:about');
    print "$about\n";
    $num_ops++;
    
    $owl->read();
    // Loop inside the rdf:Description stanza
    do {
      if ($owl->name == "rdfs:subPropertyOf") {
        $resource = $owl->getAttribute('rdf:resource');
        print $resource . "\n";
        $num_ops++;
      }
      if ($owl->nodeType == XMLReader::END_ELEMENT and $owl->name == "rdf:Description") {
        break; 
       }
    }
    while($owl->next());
  }
}
while ($owl->next());
print "Num Ops: $num_ops\n";  
