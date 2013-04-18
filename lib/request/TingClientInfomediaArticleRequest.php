<?php
class TingClientInfomediaArticleRequest extends TingClientInfomediaRequest Implements ITingClientRequestCache{
  
  /* ====== IMPLEMENTATION OF ITingClientRequestCache ======*/
  
  /** \brief ITingClientRequestCache::cacheKey; get a cachekey
   * 
   * @return string 
   */
  public function cacheKey() {
    $params = $this->getParameters();
    $ret = '';
    $this->make_cache_key($params, $ret);

    return md5($ret);
  }

  /** \brief make a cachekey based on request parameters
   *
   * @param array $params
   * @param string $ret 
   */
  private function make_cache_key($params, &$ret) {
    foreach ($params as $key => $value) {
      if (is_array($value)) {
        // recursive
        $ret.=$key;
        $this->make_cache_key($value, $ret);
      }
      else {
        $ret.=$value;
      }
    }
  }

  /** \brief ITingClientRequestCache::cacheEnable; Check if cache is enabled
   *   
   * @return value of variable (drupal_get)
   */
  public function cacheEnable($value = NULL) {
    $class_name = get_class($this);
    return variable_get($class_name . TingClientRequest::cache_enable);
  }

  /*   * \brief set timeout of cache
   * 
   * @return mixed value of variable (variable_get)  
   */

  public function cacheTimeout($value = NULL) {
    $class_name = get_class($this);
    return variable_get($class_name . TingClientRequest::cache_lifetime, '1');
  }

  /* \brief implements ITingClientRequestCache::cacheBin
   * 
   * @return string; name of cachebin
   */

  public function cacheBin() {
    return 'bibdk_cache_infomedia_webservice';
  }
  
  
  /* ====== END IMPLEMENTATION OF ITingClientRequestCache ======*/
  
  
  
   public function getRequest() {
    $this->setParameter('action', 'getArticleRequest');
    $params = array('userPinCode', 'userId', 'libraryCode','articleIdentifier','outputType');
    foreach ($params as $parameter) {
      $getter = 'get' . ucfirst($parameter);
      if ($value = $this->$getter()) {
        $this->setParameter($parameter, $value);
      }
    }
    return $this;
  }
  
  /*public function getRequest() {
    $options = array('articleIdentifier' => array('faust',),
                     'libraryCode' => 'agency',
                     'userId' => 'user',
                     'userPinCode' => 'pin',);

    $action = $this->method . self::ARTICLE . 'Request';

    $this->setParameter('action', $action);

    foreach ($options as $param => $value_name) {
      if (is_array($value_name)) {
        foreach ($value_name as $item)
          if (isset($this->$item)) {
            $this->setParameter($param, array($item => $this->$item));
            break;
          }
      }
      else
        if (isset($this->$value_name))
          $this->setParameter($param, $this->$value_name);
    }

    $this->setParameter('outputType', 'xml'); 
    return $this; 
  }*/

  /**
   * while testing. Set a user that we know is good
   */
  public function setTestUser() {
    $this->setAgency('718300');
    $this->setUser('0019');
    $this->setPin('0019');
  }
 
  /**
   * While testing - set a request that we know is good
   */
  public function setTestRequest() {
    //$action = $this->method . self::ARTICLE . 'Request';
    //$this->setParameter('action', $action);
    $this->setAgency('718300');
    $this->setFaust('33514212');
    $this->setUser('0019');
    $this->setPin('0019');
  }

  /**
   * Parse response
   * param $responseString - xml from useraccessinfomedia-webservice
   * return $TingClientInfomediaResult-object 
   */  
  public function parse($responseString) {
    $result = new TingClientInfomediaResult();
    $result->type = self::ARTICLE;
    $dom = new DOMDocument();

    //$dom->loadXML($responseString);
    if( !@$dom->loadXML($responseString) ) {
       throw new TingClientException('malformed xml in infomedia-response: ', $responseString);
    }
    $xpath = new DOMXPath($dom);
    $responseNode = '/uaim:' . $this->method . 'ArticleResponse';
    $detailsNode = '/uaim:' . $this->method . 'ArticleResponseDetails';
    $errorNode = '/uaim:error';
    #$articleNode = '/uaim:imArticle'; 

    $nodelist = $xpath->query($responseNode);

    if ($nodelist->length == 0) {     
      throw new TingClientException('TingClientInfomediaRequest got no Infomedia response: ', $responseString);
    }
 
    $errorlist = $xpath->query($responseNode . $errorNode);

    if ($errorlist->length > 0) {
      $result->error = $errorlist->item(0)->nodeValue;
      return $result;
    }
      
    $detailslist = $xpath->query($responseNode . $detailsNode);
    $result->length = $detailslist->length; 
    $identifierlist = $xpath->query($responseNode . $detailsNode . '/uaim:articleIdentifier');
    $verifiedlist = $xpath->query($responseNode . $detailsNode . '/uaim:articleVerified');

    if ($this->method == 'check') { 
      for ($i = 0; $i < $detailslist->length; $i++) {
        $identifier = $identifierlist->item($i)->nodeValue;
        $verified = $verifiedlist->item($i)->nodeValue;
        $result->parts[] = array('identifier' => $identifier, 'verified' => strcasecmp('true', $verified) == 0);
      } 
    }
    else { 
      $articlelist = $xpath->query($responseNode . $detailsNode . '/uaim:imArticle');      
      for ($i = 0; $i < $detailslist->length; $i++) {
        $identifier = $identifierlist->item($i)->nodeValue;
        $verified = $verifiedlist->item($i)->nodeValue;
        if( $verified != "false" ) {
     	  $article = $articlelist->item($i)->nodeValue;
        }
        else {
        	$article = $verifiedlist->item($i)->nodeValue;
        
        }
        $result->parts[] = array('identifier' => $identifier, 'verified' => strcasecmp('true', $verified) == 0, 'article' => $article);
      } 
    } 
    return $result;
  }
} 
