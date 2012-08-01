<?php

class TingClientRequestFactory {

  public $urls;

  public function __construct($urls) {
    $this->urls = $urls;
  }

  /**
   * @return TingClientSearchRequest
   */
  public function getSearchRequest() {
    if( isset($this->urls['search']) ) {
      return new TingClientSearchRequest($this->urls['search']);
    }
  }

  /**
   * @return TingClientScanRequest
   */
  public function getScanRequest() {
    if( isset($this->urls['scan']) ) {
      return new TingClientScanRequest($this->urls['scan']);
    }
  }

  /**
   * @return TingClientAgencyRequest
   */
  public function getAgencyRequest() {
    if( isset($this->urls['agency']) ) {
      return new TingClientAgencyRequest($this->urls['agency']);
    }
  }

  /**
   * @return TingClientCollectionRequest
   */
  public function getCollectionRequest() {
    if( isset($this->urls['collection']) ) {
      return new TingClientCollectionRequest($this->urls['collection']);
    }

  }

    /**
     * @return TingClientObjectRequest
     */
    public function getObjectRequest() {
      if( isset($this->urls['object']) ) {
	return new TingClientObjectRequest($this->urls['object']);
      }
    }

    /**
     * @return TingClientSpellRequest
     */
    public function getSpellRequest() {
      if( isset($this->urls['spell']) ) {
	return new TingClientSpellRequest($this->urls['spell']);
      }
    }

    /**
     * @return TingClientObjectRecommendationRequest
     */
    function getObjectRecommendationRequest() {
      if( isset($this->urls['recommendation']) ) {
	return new TingClientObjectRecommendationRequest($this->urls['recommendation']);
      }
    }

    /**
     * @ return TingClientInfomediaArticleRequest
     */
    function getInfomediaArticleRequest() {
      if( isset($this->urls['infomedia']) ) {
	return new TingClientInfomediaArticleRequest($this->urls['infomedia']);
      }
    }

    /**
     * @ return TingClientInfomediaReviewRequest
     */
    function getInfomediaReviewRequest() {
      if( isset($this->urls['infomedia']) ) {
	return new TingClientInfomediaReviewRequest($this->urls['infomedia']);
      }
    }
  }

