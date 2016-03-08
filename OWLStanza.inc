<?php

/**
 * @file
 * OWL redesign using Classes (Objects)
 */

Class OWLStanza {
  // The XMLReader object.
  private $owl = NULL;
  // A boolean (TRUE or FALSE) indicating if children should be parsed.
  private $parse_children = TRUE;
  // The XML element tag name (e.g. ‘owl:ObjectProperty’,
  // ‘owl:AnnotationProperty’).
  private $tag_name = '';
  // A key/value associative array of all of the attributes.
  private $attributes = array();
  // An array of OWLStanza objects.
  private $children = array();

  /**
   * Implements the constructor.
   *
   * @param $owl
   *   The XMLReader object.
   * @param $parse_children
   *   A boolean (TRUE or FALSE) indicating if children should be parsed. if
   *   FALSE only attributes will be parse.
   */
  public function __construct($owl, $parse_children = TRUE) {
    $this->owl = $owl;
    $this->parse_children = $parse_children;

  }

  /**
   * Gets the children array.
   *
   * @return
   *   An array of OWLStanza objects containing the children elements.
   */
  public function getChildren() {
    return $children;
  }

  /**
   * Sets the children array.
   *
   * @param $children
   *   An array of OWLStanza objects containing the children elements.
   * @return
   *   FALSE if the array was not set, TRUE otherwise.
   */
  public function setChildren($children) {

    // Makes sure the incoming argument is an array.
    if (!is_array($children)) {
      return FALSE;
    }

    // Make sure that all of the array elements are OWLStanza objects.
    foreach ($children as $child) {
      if (get_class($child) != 'OWLStanza') {
        return FALSE;
      }
    }

    // All is good, set the children.
    $this->children = $children;
  }
}


