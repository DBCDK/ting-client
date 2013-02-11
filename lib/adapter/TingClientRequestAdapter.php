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
    $options = array();
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
      try {
        $startTime = explode(' ', microtime());
        if ($request->getXsdNameSpace())
          $options['namespaces'] = $request->getXsdNameSpace();
        $client = new NanoSOAPClient($request->getWsdlUrl(), $options);

        // set useragent for simpletest framework
        if ($simpletest_prefix = drupal_valid_test_ua()) {
          NanoSOAPClient::setUserAgent(drupal_generate_test_ua($simpletest_prefix));
        }

        $response = $client->call($soapAction, $soapParameters);

        $stopTime = explode(' ', microtime());
        $time = floatval(($stopTime[1] + $stopTime[0]) - ($startTime[1] + $startTime[0]));

        // Logging changed to make more granular logging
        $log = array(
          '@action' => $soapAction,
          '@time' => round($time, 2),
          '@requestBody' => $client->requestBodyString,
          '@wsdlUrl' => $request->getWsdlUrl(),
        );
        $this->logger->log('Completed SOAP request @action @wsdlUrl ( @time s). Request body: @requestBody', $log);
        //$this->logger->log('Completed SOAP request ' . $soapAction . ' ' . $request->getWsdlUrl() . ' (' . round($time, 3) . 's). Request body: ' . $client->requestBodyString);

        // If using JSON and DKABM, we help parse it.
        if ($soapParameters['outputType'] == 'json') {
          return $request->parseResponse($response);
        }
        else {
          return $response;
        }
      } catch (NanoSOAPcURLException $e) {
        //Convert NanoSOAP exceptions to TingClientExceptions as callers
        //should not deal with protocol details
        throw new TingClientException($e->getMessage(), $e->getCode());
      }
    } catch (TingClientException $e) {
      // Logging changed to make more granular logging
      $log = array(
        '@action' => $soapAction,
        '@wsdlUrl' => $request->getWsdlUrl(),
        '@error' => $e->getMessage(),
      );
      $this->logger->log('Error handling SOAP request @action @wsdlUrl: @error', $log);
      //$this->logger->log('Error handling SOAP request ' . $soapAction . ' ' . $request->getWsdlUrl() . ': ' . $e->getMessage());
      throw $e;
    }
  }

}
