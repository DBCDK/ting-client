<?php

class TingClientObjectCollection {

  private $objects;

  /* @var $formattedCollection TingClientFormattedCollection */
  private $formattedCollection;

  public function __construct($objects = array()) {
    $this->objects = $objects;
  }

  /**
   * @return array
   */
  public function getObjects() {
    return $this->objects;
  }

  /**
   * @param mixed $formattedCollection
   */
  public function setFormattedCollection($formattedCollection) {
    $this->formattedCollection = $formattedCollection;
  }

  /**
   * @return TingClientFormattedCollection
   */
  public function getFormattedCollection() {
    return isset($this->formattedCollection) ? $this->formattedCollection : NULL;
  }
}

