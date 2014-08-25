<?php
class TingSoapClient{
  private $soapClient;
  public $requestBodyString;
  private $curl_info;

  public function __construct($request){
    $wsdl = $request->getWsdlUrl();
    // xdebug causes php to fail FATAL_ERROR before soapclient throws an exception
    // DISABLE.IT. It shouldn't be in production environment anyway
    if(function_exists('xdebug_disable')){
      xdebug_disable();
    }
    // soapClient is set with trace and exception options to enable proper exceptionhandling and logging
    $this->soapClient = new SoapClient($wsdl,array('trace' => 1,'exceptions'=>true, ));
  }

  public function call($action, array $params){
    return $this->send_request($action, $params);
  }

  public function getCurlInfo(){
    return $this->curl_info;
  }

  private function send_request($action,$params){
    try{
      $data = $this->soapClient->$action($params);
    }
    catch(Exception $e){
      // set status code to 500 (internal error)
      $this->set_curl_info('500');
      // rethrow. TingclientRequestAdapter catches the exception and does the logging
      throw $e;
    }
    // all went well
    $this->requestBodyString = $this->soapClient->__getLastRequest();
    $this->set_curl_info();
    return $data;
  }

  private function set_curl_info($errorcode = NULL){
    if(!empty($errorcode)){
      $this->curl_info = array('http_code'=>$errorcode);
      return;
    }
    $response = $this->soapClient->__getLastResponseHeaders();
    $this->curl_info = $this->parse_response_header($response);
    return;
  }

  /**
   * @param $headerstring string. Responsehader from soapclient
   * @return array
   */
  private function parse_response_header($headerstring){
    if(strpos($headerstring,'HTTP/1.1 200 OK')!==FALSE){
      return array('http_code'=>'200');
    }
    // status code MUST be 200
    return array('http_code' => '500');
  }
}
