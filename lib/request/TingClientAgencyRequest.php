<?php

class TingClientAgencyRequest extends TingClientRequest {

  protected $agencyName;
  protected $postalCode;
  protected $city;

  protected function getRequest() {
    $this->setParameter('action', 'pickupAgencyListRequest');

    $methodParameterMap = array(
      'agencyName' => 'agencyName',
      'postalCode' => 'postalCode',
      'city' => 'city'
    );

    foreach ($methodParameterMap as $method => $parameter) {
      $getter = 'get' . ucfirst($method);
      if ($value = $this->$getter()) {
        $this->setParameter($parameter, $value);
      }
    }

    return $this;
  }

  public function getAgencyName() {
    return $this->agencyName;
  }

  public function setAgencyName($agencyName) {
    $this->agencyName = $agencyName;
  }

  public function getPostalCode() {
    return $this->postalCode;
  }

  public function setPostalCode($postalCode) {
    $this->postalCode = $postalCode;
  }

  public function getCity() {
    return $this->city;
  }

  public function setCity($city) {
    $this->city = $city;
  }

  public function processResponse(stdClass $response) {
    $agencies = array();

    if (isset($response->pickupAgencyListResponse) && $response->pickupAgencyListResponse) {
      $response = $response->pickupAgencyListResponse;

      if (isset($response->library) && $response->library) {
        foreach ($response->library as $value) {
          $agency = new TingClientAgencyAgency();
          $agency->agencyId = $this->getValue($value->agencyId);
          $agency->agencyName = $this->getValue($value->agencyName);
          $agency->agencyPhone = $this->getValue($value->agencyPhone);
          $agency->agencyEmail = $this->getValue($value->agencyEmail);
          $agency->postalAddress = $this->getValue($value->postalAddress);
          $agency->postalCode = $this->getValue($value->postalCode);
          $agency->city = $this->getValue($value->city);

          if (isset($value->pickupAgency) && $value->pickupAgency) {
            foreach ($value->pickupAgency as $pickupAgency) {
              $branch = new TingClientAgencyBranch();

              $branch->branchId = $this->getValue($pickupAgency->branchId);
              $branch->branchName = $this->getValue($pickupAgency->branchName);
              $branch->branchPhone = $this->getValue($pickupAgency->branchPhone);
              $branch->branchEmail = $this->getValue($pickupAgency->branchEmail);
              if (isset($pickupAgency->postalAddress))
                $branch->postalAddress = $this->getValue($pickupAgency->postalAddress);
              if (isset($pickupAgency->postalCode))
                $branch->postalCode = $this->getValue($pickupAgency->postalCode);
              if (isset($pickupAgency->city))
                $branch->city = $this->getValue($pickupAgency->city);
              if (isset($pickupAgency->branchWebsiteUrl))
                $branch->branchWebsiteUrl = $this->getValue($pickupAgency->branchWebsiteUrl);
              if (isset($pickupAgency->serviceDeclarationUrl))
                $branch->serviceDeclarationUrl = $this->getValue($pickupAgency->serviceDeclarationUrl);
              if (isset($pickupAgency->openingHours))
                $branch->openingHours = $pickupAgency->openingHours;
              if (isset($pickupAgency->temporarilyClosed))
                $branch->temporarilyClosed = $this->getValue($pickupAgency->temporarilyClosed);
              if (isset($pickupAgency->userStatusUrl))
                $branch->userStatusUrl = $this->getValue($pickupAgency->userStatusUrl);
              if (isset($pickupAgency->pickupAllowed))
                $branch->pickupAllowed = $this->getValue($pickupAgency->pickupAllowed);

              $agency->pickUpAgencies[] = $branch;
            }
          }
          $agencies['libraries'][] = $agency;
        }
      } else if (isset($response->error) && $response->error) {
        $agencies['error'] = $this->getValue($response->error);
      }
    }
    return $agencies;
  }

}