<?php

/**
 * Class TingClientFormattedCollectionTestClass
 *
 * Muckup for test of TingClientFormattedCollection
 */
class TingClientFormattedCollectionTestClass extends TingClientFormattedCollection {

  public function __construct(){

  }

  public function setObjectCollection($objectCollection) {
    $this->objectCollection = $objectCollection;
  }

  public function setWorkOneTest($workDisplay) {
    $this->setWorkOne($workDisplay);
  }


  public function addRelationsToElementTest($element, $collection){
    return $this->addRelationsToElement($element, $collection);
  }

}
