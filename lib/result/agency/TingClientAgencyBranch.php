<?php

class TingClientAgencyBranch {  
  public $branchId;
  public $branchName;
  public $branchPhone;
  public $branchEmail;
  public $postalAddress;
  public $postalCode;
  public $city;
  public $branchWebsiteUrl;
  public $serviceDeclarationUrl;
  public $openingHours;
  public $temporarilyClosed;
  public $userStatusUrl;
  public $pickupAllowed;
  
  public function __construct($pickupAgency){
    
          $this->branchId = TingClientRequest::getValue($pickupAgency->branchId);
          $this->branchName = TingClientRequest::getValue($pickupAgency->branchName);
          $this->branchPhone = TingClientRequest::getValue ($pickupAgency->branchPhone);
          $this->branchEmail = TingClientRequest::getValue ($pickupAgency->branchEmail);
          if (isset($pickupAgency->postalAddress))
            $this->postalAddress = TingClientRequest::getValue ($pickupAgency->postalAddress);
          if (isset($pickupAgency->postalCode))
            $this->postalCode = TingClientRequest::getValue($pickupAgency->postalCode);
          if (isset($pickupAgency->city))
            $this->city = TingClientRequest::getValue($pickupAgency->city);
          if (isset($pickupAgency->branchWebsiteUrl))
            $this->branchWebsiteUrl = TingClientRequest::getValue($pickupAgency->branchWebsiteUrl);
          if (isset($pickupAgency->serviceDeclarationUrl))
            $this->serviceDeclarationUrl = TingClientRequest::getValue($pickupAgency->serviceDeclarationUrl);
          if (isset($pickupAgency->openingHours))
            $this->openingHours = $pickupAgency->openingHours;
          if (isset($pickupAgency->temporarilyClosed))
            $this->temporarilyClosed = TingClientRequest::getValue($pickupAgency->temporarilyClosed);
          if (isset($pickupAgency->userStatusUrl))
            $this->userStatusUrl = TingClientRequest::getValue($pickupAgency->userStatusUrl);
          if (isset($pickupAgency->pickupAllowed))
            $this->pickupAllowed = TingClientRequest::getValue($pickupAgency->pickupAllowed);
    
  }
  
}