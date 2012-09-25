<?php

class TingClientRequestFactory {

  public $urls;

  public function __construct($urls) {
    $this->urls = $urls;
  }

  /**
   * return object($className) if it exists and url is set, else throw TingClientException
   * @className, the class implementing the request
   * @name, the name of the request (for mapping in $urls variable)
   *
   * @TODO; can $url variable be refactored away??
   **/
  public function getNamedRequest($name, $className) {
    if( class_exists($className) && !empty($this->urls[$name]['url']) ) {
      return new $className($this->urls[$name]['url']);
    }
    throw new TingClientException('No webservice url or maybe class defined for ' . $name);
  }
}

