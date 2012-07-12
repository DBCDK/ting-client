<?php

class TingClientAgencyAgency {
  
  public $agencyId;
  public $agencyName;
  public $agencyPhone;
  public $agencyEmail;
  public $postalAddress;
  public $postalCode;
  public $city;
  public $pickUpAgencies;
  
  
  public function __construct() {
    $this->pickUpAgencies = array();
  }
}