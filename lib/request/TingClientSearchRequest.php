<?php

class TingClientSearchRequest extends TingClientRequest implements ITingClientRequestCache {

  /**
   * Prefix to namespace URI map.
   */
  static $namespaces = array(
    '' => 'http://oss.dbc.dk/ns/opensearch',
    'xs' => 'http://www.w3.org/2001/XMLSchema',
    'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'oss' => 'http://oss.dbc.dk/ns/osstypes',
    'dc' => 'http://purl.org/dc/elements/1.1/',
    'dkabm' => 'http://biblstandard.dk/abm/namespace/dkabm/',
    'dcmitype' => 'http://purl.org/dc/dcmitype/',
    'dcterms' => 'http://purl.org/dc/terms/',
    'ac' => 'http://biblstandard.dk/ac/namespace/',
    'dkdcplus' => 'http://biblstandard.dk/abm/namespace/dkdcplus/',
  );
  // Query parameter is required, so if not provided, we will just do a
  // wildcard search.
  protected $query = '*:*';
  protected $queryLanguage;
  protected $facets = array();
  protected $numFacets;
  protected $format;
  protected $start;
  protected $rank;
  protected $sort;
  protected $allObjects;
  protected $allRelations;
  protected $relationData;
  protected $collectionType;
  protected $agency;
  protected $profile;
  protected $includeHoldingsCount;
  var $userDefinedBoost;
  protected $userDefinedBoosts = array();
  var $userDefinedRanking;
  protected $objectFormat;
  protected $outputType;
  

  /** \brief this method is called from adapter to set parameters for a webservice call (see lib/adapter/TingClientRequestAdapter.php)
   *
   * @return \TingClientSearchRequest 
   */
  public function getRequest() {
    // These defaults are always needed.
    $this->setParameter('action', 'searchRequest');

    foreach ($this->methodParameters() as $method => $parameter) {
      $getter = 'get' . ucfirst($method);
      if ($value = $this->$getter()) {
        $this->setParameter($parameter, $value);
      }
    }

    $parameters = $this->getParameters();
    // default format
    if (!isset($parameters['objectFormat']) && empty($parameters['format'])) {
      $this->setParameter('format', 'dkabm');
    }

    // If we have facets to display, we need to construct an array of
    // them for SoapClient's benefit.
    $facets = $this->getFacets();
    if ($facets) {
      $this->setParameter('facets', array(
        'facetName' => $facets,
        'numberOfTerms' => $this->getNumFacets(),
      ));
    }

    // Include userdefinedBoost
    $this->generateBoost();
    $userDefinedBoost = $this->getUserDefinedBoost();
    if ($userDefinedBoost) {
     $this->setParameter('userDefinedBoost', $userDefinedBoost);
    }

    // Include userDefinedRanking if set on the request.
    if (is_array($this->userDefinedRanking) && !empty($this->userDefinedRanking)) {
      $this->setParameter('userDefinedRanking', $this->userDefinedRanking);
    }
    
    // numResults is not a valid parameter for search service
    $this->unsetParameter('numResults');

    return $this;
  }

  private function methodParameters() {
    $methodParameterMap = array(
      'query' => 'query',
      'queryLanguage' => 'queryLanguage',
      'format' => 'format',
      'start' => 'start',
      'numResults' => 'stepValue',
      'rank' => 'rank',
      'sort' => 'sort',
      'allObjects' => 'allObjects',
      'allRelations' => 'allRelations',
      'relationData' => 'relationData',
      'collectionType' => 'collectionType',
      'agency' => 'agency',
      'profile' => 'profile',
      'objectFormat' => 'objectFormat',
      'trackingId' => 'trackingId',
      'outputType'=>'outputType',
    );

    return $methodParameterMap;
  }

  /**
   * Generates a md5 hash based on the query value in params array.
   * @return string md5 hash
   */
  public function searchKey() {
    $params = $this->getParameters();
    return isset($params['query']) ? md5($params['query']) : '';
  }

  /** \brief Implementation of ITingClientRequestCache::cacheEnable
   *
   * get value of variable (enable/disable cache)
   */
  public function cacheEnable($value = NULL) {
    $class_name = get_class($this);
    return variable_get($class_name . TingClientRequest::cache_enable);
  }

  /** \brief Implementation of ITingClientRequestCache::cacheTimeout
   *
   * get value of variable (cachelifetime)
   */
  public function cacheTimeout($value = NULL) {
    $class_name = get_class($this);
    return variable_get($class_name . TingClientRequest::cache_lifetime, '1');
  }
  
  public function setOutputType($value){
    $this->outputType = $value;
  }
  
  public function getOutputType(){
    return $this->outputType;
  }

  /** end ITingClientRequestCache * */

  public function getCollectionType() {
    return $this->collectionType;
  }

  public function setCollectionType($collectionType) {
    $this->collectionType = $collectionType;
  }

  public function setIncludeHoldingsCount($includeHoldingsCount) {
    $this->includeHoldingsCount = $includeHoldingsCount;
  }

  public function getIncludeHoldingsCount() {
    return $this->includeHoldingsCount;
  }

  public function getQuery() {
    return $this->query;
  }

  public function setQuery($query) {
    $this->query = $query;
  }

  public function getQueryLanguage() {
    return $this->queryLanguage;
  }

  public function setQueryLanguage($queryLanguage) {
    $this->queryLanguage = $queryLanguage;
  }

  public function getFacets() {
    return $this->facets;
  }

  public function setFacets($facets) {
    $this->facets = $facets;
  }

  public function getNumFacets() {
    return $this->numFacets;
  }

  public function setNumFacets($numFacets) {
    $this->numFacets = $numFacets;
  }

  public function getUserDefinedBoost() {
    return $this->userDefinedBoosts;
  }

  public function setUserDefinedBoost($userDefinedBoosts) {
    $this->userDefinedBoosts = $userDefinedBoosts;
  }

  public function getFormat() {
    return $this->format;
  }

  public function setFormat($format) {
    $this->format = $format;
  }


  public function getObjectFormat() {
    return $this->objectFormat;
  }

  public function setObjectFormat($objectFormat) {
    $this->objectFormat = $objectFormat;
  }

  public function getStart() {
    return $this->start;
  }

  public function setStart($start) {
    $this->start = $start;
  }

  public function getRank() {
    return $this->rank;
  }

  public function setRank($rank) {
    $this->rank = $rank;
  }

  public function getSort() {
    return $this->sort;
  }

  public function setSort($sort) {
    $this->sort = $sort;
  }

  public function getAllObjects() {
    return $this->allObjects;
  }

  public function setAllObjects($allObjects) {
    $this->allObjects = $allObjects;
  }

  public function getAllRelations() {
    return $this->allRelations;
  }

  public function setAllRelations($allRelations) {
    $this->allRelations = $allRelations;
  }

  public function getRelationData() {
    return $this->relationData;
  }

  public function setRelationData($relationData) {
    $this->relationData = $relationData;
  }

  public function getAgency() {
    return $this->agency;
  }

  public function setAgency($agency) {
    $this->agency = $agency;
  }

  public function getProfile() {
    return $this->profile;
  }

  public function setProfile($profile) {
    $this->profile = $profile;
  }

  public function checkResponse($response){
    if(empty($response->numTotalCollections)) {
      // this is either an empty or facet-search. Either way cache it
      return TRUE;
    }
    else{
      // check if id is set
      $collections = $response->collections;
      foreach($collections as $collection) {
        $objects = $collection->objects;
        foreach($objects as $object){
          if(empty($object->id)){
            return FALSE;
          }
        }
      }
    }
    return TRUE;
  }

  public function processResponse(stdClass $response) {
    $searchResult = new TingClientSearchResult();
    $searchResponse = $response->searchResponse;
    if (isset($searchResponse->error)) {
      throw new TingClientException('Error handling search request: ' . self::getValue($searchResponse->error));
    }

    $searchResult->numTotalObjects = self::getValue($searchResponse->result->hitCount);
    $searchResult->numTotalCollections = self::getValue($searchResponse->result->collectionCount);
    $searchResult->more = (bool) preg_match('/true/i', self::getValue($searchResponse->result->more));

    // sortUsed is only included in result when asking for a ranking or sorting in the request
    if (isset($searchResponse->result->sortUsed)) {
      $searchResult->sortUsed =  self::getValue($searchResponse->result->sortUsed);
    }

    if (isset($searchResponse->result->searchResult) && is_array($searchResponse->result->searchResult)) {
      foreach ($searchResponse->result->searchResult as $entry => $result) {
        // pjo 22-05-12 formatted collections
        $formattedCollection = isset($result->formattedCollection) ? $result->formattedCollection : NULL;
        $searchResult->collections[] = $this->generateCollection($result->collection, (array) $response->{'@namespaces'}, $formattedCollection);
      }
    }

    if (isset($searchResponse->result->facetResult->facet) && is_array($searchResponse->result->facetResult->facet)) {
      foreach ($searchResponse->result->facetResult->facet as $facetResult) {
        $facet = new TingClientFacetResult();
        $facet->name = self::getValue($facetResult->facetName);
        if (isset($facetResult->facetTerm)) {
          foreach ($facetResult->facetTerm as $term) {
            $facet->terms[self::getValue($term->term)] = self::getValue($term->frequence);
          }
        }
        $searchResult->facets[$facet->name] = $facet;
      }
    }
    return $searchResult;
  }

  /**
   * @param $collectionData
   * @param $namespaces
   * @param null $formattedCollection
   * @return TingClientObjectCollection
   */
  private function generateCollection($collectionData, $namespaces, $formattedCollection = NULL) {
    $objects = array();
    if (isset($collectionData->object) && is_array($collectionData->object)) {
      foreach ($collectionData->object as $objectData) {
        $objects[] = $this->generateObject($objectData, $namespaces);
      }
    }

    $ret = new TingClientObjectCollection($objects);

    if (isset($formattedCollection)) {
      $ret->setFormattedCollection(new TingClientFormattedCollection($formattedCollection, $objects));
    }

    return $ret;
  }

  private function generateObject($objectData, $namespaces) {
    $object = new TingClientObject();
    if (!isset($objectData->identifier))
      return;

    if(isset($objectData->error))
      return;


    $object->id = self::getValue($objectData->identifier);

    $object->record = array();
    $object->relations = array();
    $object->formatsAvailable = array();

    // The prefixes used in the response from the server may change over
    // time. We use our own map to provide a stable interface.
    $prefixes = array_flip(self::$namespaces);

    if (isset($objectData->record)) {
      foreach ($objectData->record as $name => $elements) {
        if (!is_array($elements)) {
          continue;
        }
        foreach ($elements as $element) {
          $namespace = $namespaces[isset($element->{'@'}) ? $element->{'@'} : '$'];
          $prefix = isset($prefixes[$namespace]) ? $prefixes[$namespace] : 'unknown';
          $key1 = $prefix . ':' . $name;
          if (isset($element->{'@type'}) && strpos($element->{'@type'}->{'$'}, ':') !== FALSE) {
            list($type_prefix, $type_name) = explode(':', $element->{'@type'}->{'$'}, 2);
            $type_namespace = $namespaces[isset($type_prefix) ? $type_prefix : '$'];
            $type_prefix = isset($prefixes[$type_namespace]) ? $prefixes[$type_namespace] : 'unknown';
            $key2 = $type_prefix . ':' . $type_name;
          }
          else {
            $key2 = '';
          }
          if (!isset($object->record[$key1][$key2])) {
            $object->record[$key1][$key2] = array();
          }
          $object->record[$key1][$key2][] = $element->{'$'};
        }
      }
    }

    if (!empty($object->record['ac:identifier'][''])) {
      list($object->localId, $object->ownerId) = explode('|', $object->record['ac:identifier'][''][0]);
    }
    else {
      $object->localId = $object->ownerId = FALSE;
    }

    if (isset($objectData->relations)) {
      $object->relationsData = array();
      foreach ($objectData->relations->relation as $relation) {
        $relation_data = (object) array(
              'relationType' => $relation->relationType->{'$'},
              'relationUri' => $relation->relationUri->{'$'},
        );
        if (isset($relation->linkObject)) {
          $relation_data->linkObject = $relation->linkObject;
        }
        if (isset($relation->relationObject)) {
          $relation_object = $this->generateObject($relation->relationObject->object, $namespaces);
          $relation_data->relationObject = $relation_object;
          $relation_object->relationType = $relation_data->relationType;
          $relation_object->relationUri = $relation_data->relationUri;
          $relation_object->linkObject = isset ($relation_data->linkObject) ? $relation_data->linkObject : NULL;
          $object->relations[] = $relation_object;
        }
        $object->relationsData[] = $relation_data;
      }
    }

    if (isset($objectData->formatsAvailable)) {
      foreach ($objectData->formatsAvailable->format as $format) {
        $object->formatsAvailable[] = $format->{'$'};
      }
    }

    return $object;
  }

  /*
   * Boost language = dan and current year + last 12 years
  */
  public function generateBoost() {

    $currentYear = date("Y");
    $userDefinedBoosts[] = array(
        'fieldName' => 'term.language',
        'fieldValue' => 'dan',
        'weight' => 40,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear,
        'weight' => 20,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 1,
        'weight' => 19,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 2,
        'weight' => 18,
    );
    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 3,
        'weight' => 17,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 4,
        'weight' => 16,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 5,
        'weight' => 15,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 6,
        'weight' => 14,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 7,
        'weight' => 13,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 8,
        'weight' => 12,
    );
    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 9,
        'weight' => 11,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 10,
        'weight' => 10,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 11,
        'weight' => 9,
    );

    $userDefinedBoosts[] = array(
        'fieldName' => 'term.date',
        'fieldValue' => $currentYear - 12,
        'weight' => 8,
    );
    $this->setUserDefinedBoost($userDefinedBoosts);
  }

}
