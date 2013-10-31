<?php

/**
 * Class TingClientFormattedCollection
 */
class TingClientFormattedCollection {

  /**
   * @var stdClass
   * @deprecated TODO mmj redundant as content is held in $briefDisplay & $workDisplay
   */
  private $formattedCollection;

  /**
   * @var stdClass
   */
  private $briefDisplay;

  /**
   * @var stdClass
   */
  private $workDisplay;


  public $methods = array(
    'work' => 'returns workpart(stdobj) of formattedCollection',
    'manifestation' => 'returns array of manifestions(stdojb)',
  );

  /**
   * @param stdClass $formattedCollection
   */
  public function __construct($formattedCollection) {
    #$this->formattedCollection = $formattedCollection; //TODO mmj seems to be unsused - remove!
    if (isset($formattedCollection->workDisplay)) {
      $this->workDisplay = $formattedCollection->workDisplay;
    }
    if (isset($formattedCollection->briefDisplay)) {
      $this->briefDisplay = $formattedCollection->briefDisplay;
    }
  }

  //TODO mmj seems to be unsused - remove!
  /**
   * @return mixed
   * @deprecated
   */
  /*
  public function all() {
    return $this->formattedCollection;
  }
  */

  //TODO mmj seems to be unsused - remove!
  /**
   * @return bool
   * @deprecated
   */
  /*
    public function work() {
    if (isset($this->formattedColletion->work)) {
      return $this->formattedColletion->work;
    }

    return FALSE;
  }
  */

  //TODO mmj seems to be unsused - remove!
  /**
   * @return bool
   * @deprecated
   */
  /*
  public function manifestations() {
    if (isset($this->formattedColletion->manifestation)) {
      return $this->formattedColletion->manifestation;
    }

    return FALSE;
  }
  */

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
   * @return \stdClass
   */
  public function getSingleWorkDisplay() {
    if (!isset($this->workDisplay)) {
      return NULL;
    }
    else {
      $manifestation = $this->workDisplay->manifestation;
      if (is_array($manifestation)) {
        $manifestation = reset($manifestation);
      }
      return $manifestation;
    }
  }
}
