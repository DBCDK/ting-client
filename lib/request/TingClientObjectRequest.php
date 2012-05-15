<?php

/**
 * Get a Ting object by ID.
 *
 * Objects requests are much like search request, so this is implemented
 * as a subclass, even though it is a different request type.
 */
class TingClientObjectRequest extends TingClientRequest {
  protected $agency;
  protected $allRelations;
  protected $id;
  protected $localId;
  protected $relationData;
  protected $identifier;
  protected $profile;
  protected $outputType;
  protected $objectFormat;

  public function setObjectFormat($objectFormat) {
    $this->objectFormat = $objectFormat;
  }

  public function getObjectFormat() {
    return $this->objectFormat;
  }

  public function setOutputType($outputType) {
    $this->outputType = $outputType;
  }

  public function getOutputType() {
    return $this->outputType;
  }

  public function getProfile() {
    return $this->profile;
  }
  public function setProfile($profile) {
    $this->profile = $profile;
  }
  public function getAgency() {
    return $this->agency;
  }

  public function setAgency($agency) {
    $this->agency = $agency;
  }

  public function getAllRelations() {
    return $this->allRelations;
  }

  public function setAllRelations($allRelations) {
    $this->allRelations = $allRelations;
  }

  public function getLocalId() {
    return $this->localId;
  }

  public function setLocalId($localId) {
    $this->localId = $localId;
  }

  public function getObjectId() {
    return $this->identifier;
  }

  public function setObjectId($id) {
    $this->identifier = $id;
  }

  public function getRelationData() {
    return $this->relationData;
  }

  public function setRelationData($relationData) {
    $this->relationData = $relationData;
  }

  public function getRequest() {
    $parameters = $this->getParameters();
    // These defaults are always needed.
    $this->setParameter('action', 'getObjectRequest');

    if (!isset($parameters['objectFormat']) || empty($parameters['objectFormat'])) {
      $this->setParameter('objectFormat', variable_get('ting_search_openformat', 'dkabm'));
    }

    // Determine which id to use and the corresponding index
    if ($this->identifier) {
      $this->setParameter('identifier', $this->identifier);
    }

    // If we have both localId and ownerId, combine them to get
    elseif ($this->getAgency() && $this->localId) {
      $this->setParameter('identifier', implode('|', array(
        $this->localId,
        $this->getAgency(),
      )));
    }

    $methodParameterMap = array(
      'allRelations' => 'allRelations',
      'relationData' => 'relationData',
      'agency' => 'agency',
      'profile' => 'profile',
      'outputType' => 'outputType',
      'objectFormat' => 'objectFormat',
    );

    foreach ($methodParameterMap as $method => $parameter) {
      $getter = 'get' . ucfirst($method);
      if ($value = $this->$getter()) {
        $this->setParameter($parameter, $value);
      }
    }

    if ($allRelations = $this->getAllRelations()) {
      $this->setAllRelations($allRelations);
      $this->setRelationData($this->getRelationData());
    }

    return $this;
  }

  public function processResponse(stdClass $response) {
    // Use TingClientSearchRequest::processResponse for processing the
    // response from Ting.
    $searchRequest = new TingClientSearchRequest(NULL);
    $response = $searchRequest->processResponse($response);

    if (isset($response->collections[0]->objects[0])) {
      return $response->collections[0]->objects[0];
    }
  }
}

