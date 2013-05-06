<?php

class TingClientRequestAdapter {

  /**
   * @var TingClientLogger
   */
  protected $logger;

  function __construct($options = array()) {
    $this->logger = new TingClientVoidLogger();
  }

  public function setLogger(TingClientLogger $logger) {
    $this->logger = $logger;
  }

  public function execute(TingClientRequest $request) {

    // set options for soap request
    $options = array();
    if ($request->getXsdNameSpace())
      $options['namespaces'] = $request->getXsdNameSpace();

    //Prepare the parameters for the SOAP request
    $soapParameters = $request->getParameters();
    // Separate the action from other parameters
    $soapAction = $soapParameters['action'];
    unset($soapParameters['action']);

    // We use JSON as the default outputType.
    if (!isset($soapParameters['outputType'])) {
      $soapParameters['outputType'] = 'json';
    }
    try {

        $this->logger->startTime();

        $client = new NanoSOAPClient($request->getWsdlUrl(), $options);

        $response = $client->call($soapAction, $soapParameters);

        $this->logger->stopTime();

        // Logging changed to make more granular logging
        $log = array(
          'action' => $soapAction,
          'requestBody' => $client->requestBodyString,
          'wsdlUrl' => $request->getWsdlUrl(),
        );
        $this->logger->log('soap_request_complete', $log);
        //$this->logger->log('Completed SOAP request ' . $soapAction . ' ' . $request->getWsdlUrl() . ' (' . round($time, 3) . 's). Request body: ' . $client->requestBodyString);

      // check if http_code is a valid url
      if (method_exists($client, 'getCurlInfo')){
        $curl_info = $client->getCurlInfo();
      }


      if(isset($curl_info) && $curl_info['http_code'] != 200){
        throw new TingClientHttpStatusException('Curl returns wrong http code',  $curl_info['http_code'] );
      }

      // If using JSON and DKABM, we help parse it.
        if ($soapParameters['outputType'] == 'json') {
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
      //$this->logger->log('Error handling SOAP request ' . $soapAction . ' ' . $request->getWsdlUrl() . ': ' . $e->getMessage());
      throw $e;
    }
  }

}
