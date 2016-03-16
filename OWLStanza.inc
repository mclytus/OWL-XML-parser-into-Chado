<?php
/**
 * @file
 * OWL redesign using Classes (Objects)
 */
class OWLStanza {
  // The XMLReader object.
  private $owl = NULL;
  // A boolean (TRUE or FALSE) indicating if children should be parsed.
  private $parse_children = TRUE;
  // The XML element tag name (e.g. ‘owl:ObjectProperty’,
  // ‘owl:AnnotationProperty’).
  private $tag_name = '';
  // A key/value associative array of all of the attributes.
  private $attributes = array ();
  // An array of OWLStanza objects.
  private $children = array ();
  
  /**
   * Implements the constructor.
   *
   * @param $parse_children
   * A boolean (TRUE or FALSE) indicating if children should be parsed.
   * If FALSE only attributes will be parse.
   */
  public function __construct($owl, $parse_children = TRUE) {
    $this->owl = $owl;
    $this->parse_children = $parse_children;
    $this->parse();
  }
  
  /**
   * 
   * @param private  
   * This function is to parse each of the OWL Stanzas of the ro.owl file.
   */
  	private function parse($rdf) {
  		
  		while ($owl->nodeType != XMLReader::ELEMENT) {
  			$owl->read();
  		}
  		$this->tag_name = $owl->name;
  		  		
  		while ($owl->read());
  		
  	$about = $owl->getAttribute('rdf:about');
  	if (preg_match('/^.*\/(.*)\.owl.*$/', $about, $matches)) {
  		$db_name = strtoupper($matches[0]);
  		$this->attributes[$owl->name] = $db_name;
  	}
  	
  	if ($owl->isEmptyElement) {
  		return;
  	}
  	
  	if ($parse_children == TRUE)
  		while ($owl->read()) {
  		if ($owl->nodeType == XMLReader::ELEMENT) {
  			$child = new OWLStanza($owl);
  			$children[] = $child;
  			if ($owl->nodeType == XMLReader::END_ELEMENT and $owl->name == $this->tag_name)
			return;
				else
			return;
  
  		}
  	}
 	}
  
  
  /**
   * Gets the children array.
   *
   * @return An array of OWLStanza objects containing the children elements.
   */
  public function getChildren() {
    return $children;
  }
  /**
   * Sets the children array.
   *
   * @param $children 
   * An array of OWLStanza objects containing the children elements.
   * @return FALSE if the array was not set, TRUE otherwise.
   */
  public function setChildren($children) {
    // Makes sure the incoming argument is an array.
    if (! is_array ( $children )) {
      return FALSE;
    }
    // Make sure that all of the array elements are OWLStanza objects.
    foreach ( $children as $child ) {
      if (get_class ( $child ) != 'OWLStanza') {
        return FALSE;
      }
    }
    // All is good, set the children.
    $this->children = $children;
  }
  
  /**
   * Gets the child($tag_name) array.
   *
   * @return An array of OWLStanza objects containing the child element tag name.
   *         If the child cannot found then NULL is returned.
   */
  public function getChild($tag_name) {
    foreach ($this->children as $child) {
      if ($child->getTagName() == $tag_name) {
        return $child;
      }
    }
  }
  
  /**
   * Gets the tag name.
   *
   * @return An XML element tag name (e.g. ‘owl:ObjectProperty’,
   *         'owl:AnnotationProperty’).
   */
  public function getTagName() {
    return $tag_name;
  }
  /**
   * Sets the tag name.
   *
   * @param $tag_name The
   *          XML element tag name
   * @return value element tag name.
   */
  public function setTagName($tag_name) {
    $this->tag_name = $tag_name;
    return;
  }
  
  /**
   * Gets the attributes array.
   *
   * @return An array containing a key/value associative array
   *         of all of the attributes between the XML elements tag name.
   */
  public function getAttributes() {
    return $attributes;
  }
  
  /**
   * Sets the attributes array.
   *
   * @param $attributes An
   *          array containing a key/value associative array of all of the attributes.
   * @return FALSE if the array was not set, TRUE otherwise.
   */
  public function setAttributes($attributes) {
    // Makes sure the incoming argument is an array.
    if (! is_array ( $attributes )) {
      return FALSE;
    }
    // Make sure that all of the array are key/values.
    foreach ( $attributes as $attribute_name ) {
      if (get_class ($attribute_name ) != 'OWLStanza') {
        return FALSE;
      }
    }
    // All is good, set the attributes.
    $this->attributes = $attributes;
  }
  
  /**
   *
   * @param
   *          $attribute_name
   * @return An|NULL
   */
  public function getAttribute($attribute_name) {
    foreach ($this->attributes as $aname => $value ) {
      if ($aname == $attribute_name) {
        return $value;
      }
    }
    return NULL;
    
  }
}
 







