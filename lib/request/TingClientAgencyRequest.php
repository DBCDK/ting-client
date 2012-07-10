<?php

class TingClientAgencyRequest extends TingClientRequest {

  protected $agencyName;
  protected $postalCode;
  protected $city;

  protected function getRequest() {
    $this->setParameter('action', 'pickupAgencyListRequest');
    #$this->setParameter('outputType', 'json');
    
    $methodParameterMap = array(
      'agencyName' => 'agencyName',
      'postalCode' => 'postalCode',
      'city' => 'city'
    );
    
    foreach ($methodParameterMap as $method => $parameter) {
      $getter = 'get' . ucfirst($method);
      if ($value = $this->$getter()) {
        $this->setParameter($parameter, $value);
      }
    }
    
    return $this;
  }
  
  public function getAgencyName(){
    return $this->agencyName;
  }
  
  public function setAgencyName($agencyName){
    $this->agencyName = $agencyName;
  }
  
  public function getPostalCode(){
    return $this->postalCode;
  }
  
  public function setPostalCode($postalCode){
    $this->postalCode = $postalCode;
  }
  
  public function getCity(){
    return $this->city;
  }
  
  public function setCity($city){
    $this->city = $city;
  }
  
  public function processResponse(stdClass $response) {
    return $response;
  }
}