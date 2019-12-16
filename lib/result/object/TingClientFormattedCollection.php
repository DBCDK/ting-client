<?php

/**
 * Class TingClientFormattedCollection
 */
class TingClientFormattedCollection {

  /**
   * @var stdClass
   */
  protected $briefDisplay;

  /**
   * @var stdClass
   */
  protected $workDisplay;

  /* @var stdClass $workOne */
  protected $workOne;

  /* @var array */
  protected $objectCollection;

  /**
   * @param stdClass $formattedCollection
   */
  public function __construct($formattedCollection, $objectCollection = array()) {
    $this->objectCollection = $objectCollection;
    if (isset($formattedCollection->workDisplay)) {
      $this->workDisplay = $formattedCollection->workDisplay;
      $this->setWorkOne($formattedCollection->workDisplay);
    }
    else {
      if (isset($formattedCollection->bibdkWorkDisplay)) {
        $this->workDisplay = $formattedCollection->bibdkWorkDisplay;
        $this->setWorkOne($formattedCollection->bibdkWorkDisplay);
      }
    }
    if (isset($formattedCollection->briefDisplay)) {
      $this->briefDisplay = $formattedCollection->briefDisplay;
    }
  }

  /**
   * @return \stdClass
   */
  public function getBriefDisplay() {
    return isset($this->briefDisplay) ? $this->briefDisplay : NULL;
  }

  /**
   * @return \stdClass
   */
  public function getWorkDisplay() {
    return isset($this->workDisplay) ? $this->workDisplay : NULL;
  }

  /**
   * @param \stdClass $workDisplay
   */
  protected function setWorkOne($workDisplay) {
    if (!isset($workDisplay->manifestation)) {
      return;
    }

    $manifestations = $workDisplay->manifestation;

    if (!is_array($manifestations)) {
      $manifestations = array($manifestations);
    }

    foreach ($manifestations as $manifestation) {
      if (is_object($manifestation)) {
        $this->workOne = $this->addRelationsToElement($manifestation, $this->objectCollection);
        break;
      }
    }
  }


  /**
   * Add relation data to the work one manifestation
   *
   * @param $element
   * @param $collection
   */
  protected function addRelationsToElement($element, $collection) {
    if (empty($collection)) {
      return $element;
    }


    $object = current($collection);

    if (isset($object->relationsData)) {
      $element->relationsData = $object->relationsData;
    }

    return $element;
  }

  /**
   * @return \stdClass
   */
  public function getWorkOne() {
    return isset($this->workOne) ? $this->workOne : NULL;
  }
}
