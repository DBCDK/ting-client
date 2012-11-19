<?php

class TingClientFormattedCollection {

  public $formattedCollection;
  public $methods =
      array('work' => 'returns workpart(stdobj) of formattedCollection',
    'manifestation' => 'returns array of manifestions(stdojb)',);

  public function __construct($formattedCollection) {
    if (isset($formattedCollection->workDisplay)) {
      $this->formattedCollection = $formattedCollection->workDisplay;
    } elseif(isset($formattedCollection->briefDisplay)){
      $this->formattedCollection = $formattedCollection->briefDisplay;
    }
  }

  public function all() {
    return $this->formattedCollection;
  }

  public function work() {
    if (isset($this->formattedColletion->work)) {
      return $this->formattedColletion->work;
    }
    return FALSE;
  }

  public function manifestations() {
    if (isset($this->formattedColletion->manifestation)) {
      return $this->formattedColletion->manifestation;
    }
    return FALSE;
  }
}

?>
