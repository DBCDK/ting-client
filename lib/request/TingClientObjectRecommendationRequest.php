<?php

class TingClientObjectRecommendationRequest extends TingClientRequest {
  const GENDER_MALE = 'male';
  const GENDER_FEMALE = 'female';

  protected $id;
  protected $isbn;
  protected $pid;
  protected $localId;
  protected $numResults;
  protected $gender;
  protected $minAge;
  protected $maxAge;
  protected $fromDate;
  protected $toDate;

  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }

  public function getIsbn() {
    return $this->isbn;
  }

  public function setIsbn($isbn) {
    $this->isbn = $isbn;
  }

  public function getPid() {
    return $this->pid;
  }

  public function setPid($pid) {
    $this->isbn = $pid;
  }

  public function setLocalId($localId) {
    $this->localId = $localId;
  }

  public function getLocalId() {
    return $this->localId;
  }

  function getNumResults() {
    return $this->numResults;
  }

  function setNumResults($numResults) {
    $this->numResults = $numResults;
  }

  public function getGender() {
    return $this->gender;
  }

  public function setGender($gender) {
    $this->gender = $gender;
  }

  public function getAge() {
    return array($this->minAge, $this->maxAge);
  }

  public function setAge($minAge, $maxAge) {
    $this->minAge = $minAge;
    $this->maxAge = $maxAge;
  }

  public function getDate() {
    return array($this->fromDate, $this->toDate);
  }

  public function setDate($fromDate, $toDate) {
    $this->fromDate = $fromDate;
    $this->toDate = $toDate;
  }

  protected function getRequest() {
    $this->setParameter('action', 'adhlRequest');

    if ($this->id) {
      $this->setParameter('id', $this->id);
    }

    if ($this->numResults) {
      $this->setParameter('numRecords', $this->numResults);
    }

    if ($this->gender) {
      switch ($this->gender) {
        case TingClientObjectRecommendationRequest::GENDER_MALE:
          $gender = 'm';
          break;
        case TingClientObjectRecommendationRequest::GENDER_FEMALE:
          $gender = 'k';
      }
      $this->setParameter('gender', $gender);
    }

    if ($this->minAge || $this->maxAge) {
      $minAge = ($this->minAge) ? $this->minAge : 0;
      $maxAge = ($this->maxAge) ? $this->maxAge : 100;
      $this->setParameter('minAge', $minAge);
      $this->setParameter('maxAge', $maxAge);
    }

    if ($this->fromDate || $this->toDate) {
      $this->setParameter('from', $this->fromDate);
      $this->setParameter('to', $this->toDate);
    }

    return $this;
  }

  public function processResponse(stdClass $response) {
    if (isset($response->error)) {
      throw new TingClientException('Error handling recommendation request: '.$response->error);
    }

    return $response->adhlResponse;
  }
}

