<?php

/**
 * Class TingClientFormattedCollection
 */
class TingClientFormattedCollection {

  /**
   * @var stdClass
   */
  private $briefDisplay;

  /**
   * @var stdClass
   */
  private $workDisplay;

  /* @var stdClass $workOne*/
  private $workOne;

  /**
   * @param stdClass $formattedCollection
   */
  public function __construct($formattedCollection) {
    if (isset($formattedCollection->workDisplay)) {
      $this->workDisplay = $formattedCollection->workDisplay;
      $this->setWorkOne($formattedCollection->workDisplay);
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
  public function setWorkOne($workDisplay) {
    $manifestations = $workDisplay->manifestation;

    if(!is_array($manifestations)){
      $manifestations = array($manifestations);
    }

    $workOne = NULL;
    foreach ($manifestations as $manifestation) {
      if(!is_null($manifestation) && is_object($manifestation)){
        $workOne = $manifestation;
        break;
      }
    }

    $this->workOne = $workOne;
  }


  /**
   * @return \stdClass
   */
  public function getWorkOne() {
    return isset($this->workOne) ? $this->workOne : NULL;
  }
}
