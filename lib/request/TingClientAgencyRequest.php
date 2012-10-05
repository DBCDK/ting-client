<?php

class TingClientAgencyRequest extends TingClientRequest implements ITingClientRequestCache{

  protected $agencyName;
  protected $postalCode;
  protected $city;


  protected $cacheKey;

  protected function getRequest() {
    $this->setParameter('action', 'pickupAgencyListRequest');
    $this->setParameter('libraryType', '');

    $methodParameterMap = array(
      'agencyName' => 'agencyName',
      'postalCode' => 'postalCode',
      'city' => 'city',
    );

    foreach ($methodParameterMap as $method => $parameter) {
      $getter = 'get' . ucfirst($method);
      if ($value = $this->$getter()) {
        $this->setParameter($parameter, $value);
      }
    }

    return $this;
  }

 /** Implementation of ITingClientRequestCache **/
  public function cacheKey() { 
    if( !isset($this->cacheKey) ) {
      $this->cacheKey.= 'agencyName'.$this->getAgencyName().'postalCode'.$this->getPostalCode().'city'.$this-> getCity();
    }
    return md5($this->cacheKey);
  } 

  public function cacheEnable($value=NULL) {
    $class_name = get_class($this);
    return variable_get($class_name.TingClientRequest::cache_enable);
  }

  public function cacheTimeout($value=NULL) {
    $class_name = get_class($this);
    return variable_get($class_name.TingClientRequest::$cache_lifetime,'1');
  }

  /** end ITingClientRequestCache **/

  public function getAgencyName() {
    return $this->agencyName;
  }

  public function setAgencyName($agencyName) {
    $this->agencyName = $agencyName;
  }

  public function getPostalCode() {
    return $this->postalCode;
  }

  public function setPostalCode($postalCode) {
    $this->postalCode = $postalCode;
  }

  public function getCity() {
    return $this->city;
  }

  public function setCity($city) {
    $this->city = $city;
  }

  public function processResponse(stdClass $response) {

    if (isset($response->pickupAgencyListResponse) && $response->pickupAgencyListResponse) {
      $response = $response->pickupAgencyListResponse;

      if (isset($response->library) && $response->library) {
        $agencies = $this->parseResult($response);
      }
      else if (isset($response->error) && $response->error) {
        $agencies['error'] = $this->getValue($response->error);
      }
    }
    return $agencies;
  }

  /**
   * Parsing the response
   * @param type $response
   * @return \TingClientAgencyAgency 
   */
  private function parseResult($response) {
    $agencies = array();
    $counter = 0;
    foreach ($response->library as $value) {
      $agency = new TingClientAgencyAgency();
      if(isset($value->agencyId))
        $agency->agencyId = $this->getValue($value->agencyId);
      if(isset($value->agencyName))
        $agency->agencyName = $this->getValue($value->agencyName);
      if(isset($value->agencyPhone))
        $agency->agencyPhone = $this->getValue($value->agencyPhone);
      if(isset($value->agencyEmail))
        $agency->agencyEmail = $this->getValue($value->agencyEmail);
      if(isset($value->postalAddress))
        $agency->postalAddress = $this->getValue($value->postalAddress);
      if(isset($value->postalCode))
        $agency->postalCode = $this->getValue($value->postalCode);
      if(isset($value->city))
        $agency->city = $this->getValue($value->city);

      if (isset($value->pickupAgency) && $value->pickupAgency) {
        foreach ($value->pickupAgency as $pickupAgency) {
          $branch = new TingClientAgencyBranch($pickupAgency);
          $agency->pickUpAgencies[] = $branch;
          $counter++;
        }
      }
      $agencies['libraries'][] = $agency;
    }
    $agencies['count'] = $counter;
    return $agencies;
  }

}
