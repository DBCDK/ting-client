<?php

/**
 * @file
 * Class TingRestClient
 *
 */

 /**
 * Constructor. Initialize TingRestClient.
 *
 * @param TingCLientRequest $request
 *
 * @throws \SoapFault
 */
class TingRestClient extends MicroCURL implements TingClientAgentInterface {

  /**
   * @var TingClientRequest
   */
  protected $request;

  /**
   * Adapter type for log messages.
   * @var string
   */
  public $requestAdapter = 'REST';

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
   * CURL options.
   * @var array
   */
  private $curlOptions = NULL;

  /**
   * Http method.
   * @var string
   */
  private $httpMethod = 'GET';

  public function __construct($request) {
    parent::__construct();
    $this->request = $request;
    $this->curlOptions = $request->curlOptions;
    $this->httpMethod = (!empty($this->curlOptions[CURLOPT_POST]) && $this->curlOptions) ? 'POST' : 'GET';
  }

  public function call($action, $params) {
    $outputType = (!empty($params['outputType'])) ? $params['outputType'] : 'json';
    
    if ($this->curlOptions) {
      try {
        $this->set_multiple_options($this->curlOptions);
      }
      catch (TingClientSoapException $e) {
        $this->logger->log('request_error', array('Exception : ' => $e->getMessage() ));
      }
    }

    if ($this->httpMethod == 'POST') {
      $url = $this->request->getWsdlUrl();
      $this->requestBodyString = $this->getRequestBodyString();
    }
    else {
      $this->requestVariables = http_build_query($params);
      $url = $this->request->getWsdlUrl() . '?' . $this->requestVariables;
    }
    
    $this->set_url($url);
    $response = $this->get();
    $status = $this->get_status();
    if ($status['errno'] != '0') {
      switch ($outputType) {
        case 'xml':
          // TO DO: XML error document.
        default:
          $errorObject = new stdClass();
          $errorObject->error = $status['errno'];
          $errorObject->errorMessage = $status['error'];
          return json_encode($errorObject);
      }
    }
    else if ($status['http_code'] != '200') {
      switch ($outputType) {
        case 'xml':
          // TO DO: XML error document.
        default:
          $errorObject = new stdClass();
          $errorObject->error = $status['http_code'];
          $errorObject->errorMessage = $response;
          return json_encode($errorObject);
      }
      
    }
    return $response;
  }

  public function getCurlInfo() {
    return $this->get_status();
  }

  /**
   * Return requestBodyString (for logging request).
   *
   * @return string
   */
  public function getRequestBodyString() {
    if (!empty($this->curlOptions[CURLOPT_POST]) && $this->curlOptions[CURLOPT_POST]) {
      $post = $this->curlOptions[CURLOPT_POSTFIELDS];
      if (!empty($post)) {
        return $post;
      }
    }
    return 'NULL';
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