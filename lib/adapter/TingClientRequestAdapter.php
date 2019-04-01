<?php

//require_once('lib/soapClient/TingSoapClient.php');
class TingClientRequestAdapter {

  /**
   * @var TingClientLogger
   */
  protected $logger;
  private $client;

  function __construct($options = array()) {
    $this->logger = new TingClientVoidLogger();
  }

  public function setLogger(TingClientLogger $logger) {
    $this->logger = $logger;
  }

  /* Prepare the parameters for the request.
   * @param TingClientRequest $request
   * @param string $client_type
   * @return array
   */
  private function prepare_client_params($request, $client_type = NULL) {
    /** @var TingClientRequest $request */
    $parameters = $request->getParameters();
    switch ($client_type) {
      case 'NANO':
        if ($request->defaultOutputType()) {
          // JSON is the default outputType.
          if (!isset($parameters['outputType'])) {
            $parameters['outputType'] = 'json';
          }
        }
    }
    return $parameters;
  }

  private
  function set_client_and_params($request) {
    /** @var TingClientRequest $request */
    $client_type = $request->getClientType();
    $parameters = $this->prepare_client_params($request, $client_type);
    switch ($client_type) {
      case 'SOAPCLIENT':
        try {
          $this->client = new TingSoapClient($request);
        } catch (Exception $e) {
          throw new TingClientSoapException($e->getMessage());
        }
        break;
      case 'NANO':
        // set options for NanoSoap @see contrib/Nanosoap
        $options = array();
        if ($request->getXsdNameSpace()) {
          $options['namespaces'] = $request->getXsdNameSpace();
        }
        $this->client = new TingNanoSOAPClient($request->getWsdlUrl(), $options);
        break;
      case 'REST':
        try {
          $this->client = new TingRestClient($request);
        } catch (Exception $e) {
          throw new TingClientRestException($e->getMessage());
        }
        break;
      default:
        $class_name = get_class($request);
        throw new TingClientSoapException('No or wrong client type given for ' . $class_name);
        break;
    }
    return $parameters;
  }

  /**
   * @param TingClientRequest $request
   * @return mixed
   * @throws Exception
   */
  public
  function execute(TingClientRequest $request) {
    try {
      try {
        $soapParameters = $this->set_client_and_params($request);
        if (isset($soapParameters['action'])) {
          $soapAction = $soapParameters['action'];
          unset($soapParameters['action']);
        }
        else {
          $soapAction = $request->getAction();
        }
      } catch (TingClientSoapException $e) {
        $soapAction = $request->getAction();
        throw new TingClientException($e->getMessage());
      }

      $this->logger->startTime();

      $response = $this->client->call($soapAction, $soapParameters);

      $this->logger->stopTime();

      // Logging changed to make more granular logging
      $log = array(
        'action' => $soapAction,
        'adapter' => $this->client->getRequestAdapter(),
        'requestBody' => $this->client->getRequestBodyString(),
        'requestVariables' => $this->client->getRequestVariables(),
        'wsdlUrl' => $request->getWsdlUrl(),
      );
      $this->logger->log('soap_request_complete', $log);

      // check if http_code is a valid url
      $curl_info = array();
      if (method_exists($this->client, 'getCurlInfo')) {
        $curl_info = $this->client->getCurlInfo();
      }

      if ($curl_info['http_code'] != 200) {
        $response = json_decode($response);
        // Open Platform has more detailed error description.
        $error = $error_description = NULL;
        if (!empty($response->errorMessage) && $errorMessage = json_decode($response->errorMessage)) {
          $error = (!empty($errorMessage->error)) ? '. ' . $errorMessage->error : NULL;
          $error_description = (!empty($errorMessage->error_description)) ? '. ' . $errorMessage->error_description : NULL;
        }
        throw new TingClientHttpStatusException('Curl returns wrong http code (' . $curl_info['http_code'] . ')' . $error . $error_description, $curl_info['http_code']);
      }

      // If using JSON and DKABM, we help parse it.
      if (isset($soapParameters['outputType']) && $soapParameters['outputType'] == 'json') {
        return $request->parseResponse($response);
      }
      else {
        return $response;
      }
    } catch (Exception $e) {
      // Logging changed to make more granular logging
      $log = array(
        'action' => $soapAction,
        'wsdlUrl' => $request->getWsdlUrl(),
        'error' => $e->getMessage(),
      );
      $this->logger->log('soap_request_error', $log);
      throw $e;
    }
  }

}
