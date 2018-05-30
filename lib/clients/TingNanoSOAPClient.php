<?php

/**
 * @file
 * Class TingNanoSOAPClient
 *
 */

 /**
 * Constructor. Initialize TingNanoSOAPClient.
 *
 * @param TingCLientRequest $request
 *
 * @throws \SoapFault
 */
class TingNanoSOAPClient extends NanoSOAPClient implements TingClientAgentInterface {

  /**
   * Adapter type for log messages.
   * @var string
   */
  public $requestAdapter = 'NanoSOAP';

  /**
   * The string sent as part of a request body.
   * @var string
   */
  public $requestBodyString = NULL;

  /**
   * Request variables for log messages.
   * @var string
   */
  public $requestVariables = NULL;


  /**
   * Construct the SOAP client, using the specified options.
   */
  function __construct($endpoint, $options = array()) {
    $this->endpoint = $endpoint;
    if (isset($options['namespaces']) && $options['namespaces']) {
      $this->namespaces = $options['namespaces'] + array('SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/');
    }
    else {
      $this->namespaces = array('SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/');
    }
  }


  /**
   * Return requestBodyString (for logging request).
   *
   * @return string
   */
  public function getRequestBodyString() {
    return $this->requestBodyString;
  }


  /**
   * Return getRequestVariables (for logging request).
   *
   * @return string
   */
  public function getRequestVariables() {
    return $this->requestVariables;
  }


  /**
   * Return getRequestAdapter (for logging request).
   *
   * @return string
   */
  public function getRequestAdapter() {
    return $this->requestAdapter;
  }
  
}