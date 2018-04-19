<?php

class TingClientOpenPlatformRequest extends TingClientRequest  {

  /* 
   * we include CURL options in the request, in order to be able configure MicroCURL, 
   * but the params are unset before we do TingRestClient->call().
   */
  public function getCurlOptions() {
    return $this->curlOptions;
  }

  public function setCurlOptions($options) {
    $this->curlOptions = $options;
  }

  public function getPids() {
    return $this->pids;
  }

  public function setPids($pids) {
    $this->pids = $pids;
  }

  public function getNumRecords() {
    return $this->numRecords;
  }

  public function setNumRecords($numRecords) {
    $this->numRecords = $numRecords;
  }

  public function setAction($value = NULL) {
    $this->action = $value;
  }

  public function getAction() {
    return $this->action;
  }

  /* 
   * Extend getRequest() to avoid xsd validation error.
   */
  public function getRequest() {
    $this->setParameter('action', 'getOpenPlatformRequest');
    $params = array('pids', 'numRecords');
    foreach ($params as $parameter) {
      $getter = 'get' . ucfirst($parameter);
      if ($value = $this->$getter()) {
        $this->setParameter($parameter, $value);
      }
    }
    return $this;
  }
  
  /* 
   * Extend getClientType() to use Open Platform adapter.
   */
  public function getClientType() {
    return 'REST';
  }

  public function processResponse(stdClass $response) {
dpm(__FUNCTION__);
    return json_decode($response);
  }

}
