<?php

class TingClientAgencyRequest extends TingClientRequest implements ITingClientRequestCache {

  protected $anyField;
  protected $cacheKey;

  protected function getRequest() {
    $this->setParameter('action', 'findLibraryRequest');
    $this->setParameter('libraryType', '');

    $methodParameterMap = array(
      'anyField' => 'anyField',
    );

    foreach ($methodParameterMap as $method => $parameter) {
      $getter = 'get' . ucfirst($method);
      if ($value = $this->$getter()) {
        $this->setParameter($parameter, $value);
      }
    }
    return $this;
  }

  /** Implementation of ITingClientRequestCache * */
  public function cacheKey() {
    if (!isset($this->cacheKey)) {
      $this->cacheKey.= '$anyField' . $this->getAnyField();
    }
    return md5($this->cacheKey);
  }

  public function cacheEnable($value = NULL) {
    $class_name = get_class($this);
    return variable_get($class_name . TingClientRequest::cache_enable);
  }

  public function cacheTimeout($value = NULL) {
    $class_name = get_class($this);
    return variable_get($class_name . TingClientRequest::cache_lifetime, '1');
  }

  /** end ITingClientRequestCache * */
  public function getAnyField() {
    return $this->anyField;
  }

  public function setAnyField($anyField) {
    $this->anyField = $anyField;
  }

  public function processResponse(stdClass $response) {
    if (isset($response->findLibraryResponse) && $response->findLibraryResponse) {
      $response = $response->findLibraryResponse;

      if (isset($response->pickupAgency) && $response->pickupAgency) {
        $agencies = $this->parseResult($response->pickupAgency);
      }
      else if (isset($response->error) && $response->error) {
        $agencies['error'] = $this->getValue($response->error);
      }
      else if (!isset($response->error) && !isset($response->pickupAgency)) {
        return $agencies['error'] = t('no_libraries_found_and_no_errors_reported');
      }
    }
    return $agencies;
  }

  /**
   * Parsing the response
   * @param type $response
   * @return array of TingClientAgencyBranch objects
   */
  private function parseResult($response) {
    $agencies = array();
    $counter = 0;
    foreach ($response as $value) {
      $agency = new TingClientAgencyBranch($value);
      $agencies['libraries'][] = $agency;
      $counter++;
    }
    $agencies['count'] = $counter;
    return $agencies;
  }

}