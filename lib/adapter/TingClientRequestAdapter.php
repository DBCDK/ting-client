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

  private function prepate_nano_client_params($request){
    //Prepare the parameters for the SOAP request
    $soapParameters = $request->getParameters();

    // We use JSON as the default outputType.
    if (!isset($soapParameters['outputType'])) {
      $soapParameters['outputType'] = 'json';
    }
    return $soapParameters;

  }

  private function prepare_soap_client_params($request){
    return $request->getParameters();
  }

  private function set_client_and_params($request){
    $client_type = $request->getClientType();
    switch($client_type){
      case 'SOAPCLIENT':
        try{
          $this->client = new TingSoapClient($request);
        }
        catch(Exception $e){
          throw new TingClientSoapException($e->getMessage());
        }
        $soapParameters = $this->prepare_soap_client_params($request);
        break;
      case 'NANO':
        // set options for NanoSoap @see contrib/Nanosoap
        $options = array();
        if ($request->getXsdNameSpace()){
          $options['namespaces'] = $request->getXsdNameSpace();
        }
        $this->client = new NanoSOAPClient($request->getWsdlUrl(), $options);
        $soapParameters = $this->prepate_nano_client_params($request);
        break;
      default:
        $class_name = get_class($request);
        throw new TingClientSoapException('No or wrong client type given for '.$class_name);
        break;
    }

    return $soapParameters;
  }

  public function execute(TingClientRequest $request) {
    try {
      try{
        $soapParameters = $this->set_client_and_params($request);
        if(isset($soapParameters['action'])){
          $soapAction = $soapParameters['action'];
          unset($soapParameters['action']);
        }
        else{
          $soapAction = $request->getAction();
        }
      }
      catch(TingClientSoapException $e){
        $soapAction = $request->getAction();
        throw new TingClientException($e->getMessage());
      }

      $this->logger->startTime();

      $response = $this->client->call($soapAction, $soapParameters);

      $this->logger->stopTime();

      // Logging changed to make more granular logging
      $log = array(
        'action' => $soapAction,
        'requestBody' => $this->client->requestBodyString,
        'wsdlUrl' => $request->getWsdlUrl(),
      );
      $this->logger->log('soap_request_complete', $log);
      //$this->logger->log('Completed SOAP request ' . $soapAction . ' ' . $request->getWsdlUrl() . ' (' . round($time, 3) . 's). Request body: ' . $client->requestBodyString);

      // check if http_code is a valid url
      if (method_exists($this->client, 'getCurlInfo')){
        $curl_info = $this->client->getCurlInfo();
      }

      if($curl_info['http_code'] != 200){
        throw new TingClientHttpStatusException('Curl returns wrong http code ('.$curl_info['http_code'].')',  $curl_info['http_code'] );
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
      //$this->logger->log('Error handling SOAP request ' . $soapAction . ' ' . $request->getWsdlUrl() . ': ' . $e->getMessage());
      throw $e;
    }
  }

}
