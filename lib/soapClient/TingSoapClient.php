<?php
class TingSoapClient{
  private $soapClient;
  public $requestBodyString;
  private $curl_info;
  // for test purpose
  public static $user_agent;

  public function __construct($request, $location = NULL){
    // get uri of wsdl
    $wsdl = $request->getWsdlUrl();
    // soapClient is set with trace and exception options to enable proper exceptionhandling and logging
    $options = array(
      'trace' => 1,
      'exceptions'=> 1,
      'soap_version'=> SOAP_1_1,
      'cache_wsdl' => WSDL_CACHE_NONE,
    );

    if(isset(self::$user_agent)){
      $options += array('user_agent'=>self::$user_agent);
    }

    if(!empty($location)) {
      $options += array('location'=>$location);
    }

    try{
      // developer note: xdebug causes soapclient to fail FATAL before
      // soapclient throws an exception - disable it. it shouldn't be in
      // production anyways
      if(function_exists('xdebug_disable')){
        xdebug_disable();
      }
      $this->soapClient = @new SoapClient($wsdl,$options);
    }
    catch(Exception $e){
      // set status code to 500 (server error)
      $this->set_curl_info('500');
    }
  }

  public function call($action, $params){
    $data = $this->send_request($action, $params);
    return $data;
  }

  public function getCurlInfo(){
    return $this->curl_info;
  }

  private function send_request($action,$params){
    try{
      $data = $this->soapClient->$action($params);
    }
    catch(Exception $e){
      // set status code to 400 (bad request)
      $this->set_curl_info('400');
      return;
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
    $responseHeaders = $this->soapClient->__getLastResponseHeaders();
    $this->curl_info = $this->parse_response_header($responseHeaders);
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
