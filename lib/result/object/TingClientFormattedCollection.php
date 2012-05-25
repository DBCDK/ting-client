<?php
class TingClientFormattedCollection {
  public $formattedCollection;

  public $methods = 
    array('work'=>'returns workpart(stdobj) of formattedCollection',
	  'manifestation' => 'returns array of manifestions(stdojb)',);

  public function __construct( $formattedCollection){
    $this->formattedCollection = $formattedCollection->workDisplay->workDisplay;
  }

  public function work() {
    if (isset( $this->formattedColletion->work ) ) {
      return $this->formattedColletion->work;
    }
    return FALSE;
  }

  public function manifestations() {
    if( isset($this->formattedColletion->manifestation) ) {
      return $this->formattedColletion->manifestation;
    }
    return FALSE;
  }
}

?>