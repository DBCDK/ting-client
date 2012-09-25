<?php

class TingClientAgencyServiceRequest extends TingClientRequest implements ITingClientRequestCache{

  protected $agencyId;
  protected $service;
  

  protected $cacheKey;

  protected function getRequest() {
    $this->setParameter('action', 'serviceRequest');
    
    $methodParameterMap = array(
      'agencyId' => 'agencyId',
      'service' => 'service',
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
      $this->cacheKey.= 'service'.$this->getService().'postalCode'.$this->getAgencyID();
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
  
  /** Getters and setters**/

  public function getAgencyId() {
    return $this->agencyId;
  }

  public function setAgencyId($agencyId) {
    $this->agencyId = $agencyId;
  }
  
  public function getService() {
    return $this->service;
  }

  public function setService($service) {
    $this->service = $service;
  }
  
    /** End Getters and setters**/
  
  public function processResponse(stdClass $response) {
    $service = $this->service;
    if (isset($response->serviceResponse)) {
      $response = $response->serviceResponse;

      if (isset($response->$service)) {
        $result = $response->$service;
      }
      else if (isset($response->error) && $response->error) {
        $result['error'] = $this->getValue($response->error);
      }
    }
    return $result;
  }

}
