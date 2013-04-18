<?php
abstract class TingClientInfomediaRequest extends TingClientRequest  {
  const ARTICLE = 'getArticle';
  const REVIEW  = 'Review';
  protected $method;
  protected $type;
  protected $userPinCode;
  protected $userId;
  protected $faust;
  protected $articleIdentifier;
  protected $libraryCode;
  protected $outputType;
  
  public function getOutputType(){
    return $this->outputType;    
  }
  
  public function setOutputType($value) {
    $this->outputType = $value;
  }
    
  
  public function getArticleIdentifier(){
    return $this->articleIdentifier;
  }
  
  public function setArticleIdentifier(array $value){
    $this->articleIdentifier = $value;
  }
  
  public function makeGet() {
    $this->method = 'get';
  }

  public function makeCheck() {
    $this->method = 'check';
  }

  public function getMethod() {
    return $this->method;
  }

  public function setLibraryCode($agency) {
    $this->libraryCode = $agency;
  }

  public function getLibraryCode() {
    return $this->libraryCode;
  }

  public function setUserPinCode($pin) {
    $this->userPinCode = $pin;
  }

  public function getUserPinCode() {
    return $this->userPinCode;
  }

  public function setUserId($user) {
    $this->userId = $user;
  }

  public function getUserId() {
    return $this->userId;
  }

  public function setFaust($faust) {
    $this->faust = $faust;
  }

  public function getFaust() {
    return $this->faust;
  } 

  public function processResponse(stdClass $response) {
    return $response;
  } 

  public function parseResponse($responseString) {
    return $responseString;
  }
}
