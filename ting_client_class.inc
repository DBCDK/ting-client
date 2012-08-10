<?php

/* \brief
 * class depends heavily on ting-client module.
 * builds requests for opensearch, openscan, openspell, openadhl and useraccessinfomedia webservices
 */

class ting_client_class {
  private static $request_factory;
  private static $ting_client;
  private $enable_cache;

  // @TODO ; construct parameters ? -- objectFormat etc.
  public function __construct() {
    $this->enable_cache = variable_get('search_client_enable_cache',FALSE);
  }

  /**
   * initialize a search; call execute
   * method uses a TingClientSearchRequest as parameter
   **/
  public function do_search($params) {
    // get the searchrequest
    $request = self::request_factory()->getSearchRequest();
    // set parameters NOTICE: searchrequest MUST implement method e.g parameters['format'] => setFormat
    foreach ($params as $key => $value) {
      $setter = 'set' . ucfirst($key);
      if( method_exists( $request,$setter ) ){
        $request->$setter($value);
      }
      else {
        drupal_set_message('TingClientSearchRequest does not implement: '.$setter,'error');
        watchdog('Openformat search','TingClientSearchRequest does not implement %setter called from %file in line %line',array('%setter'=>$setter,'%file'=>__FILE__ ,'%line'=>__LINE__),WATCHDOG_ERROR);
      }
    }
    $result = $this->execute($request);

    if( $result ) {
    // hand cachekey to calling method
       $result->cacheKey = $request->cacheKey();
    }

    return $result;
  }

  public function do_agency_search($params){
    // get the agencyrequest
    $request = self::request_factory()->getAgencyRequest();

    // set parameters NOTICE: searchrequest MUST implement method e.g parameters['format'] => setFormat
    foreach ($params as $key => $value) {
      $setter = 'set' . ucfirst($key);
      if( method_exists( $request,$setter ) ){
        $request->$setter($value);
      }
      else {
        drupal_set_message('TingClientAgencyRequest does not implement: '.$setter,'error');
      }
    }
    $result = $this->execute($request);

    return $result;
  }

  private function get_from_cache($request,&$cache) {
    // overall caching variable (for all requests)
    $requestcache = $this->enable_cache;
    if( $requestcache && self::_class_implements( $request, 'ITingClientRequestCache' ) ) {
       if($request->cacheEnable()){
        // everything is okay for caching; set passed cache variable for execute method
        $cache = TRUE;
        $cached_result = cache_get($request->cacheKey(),'cache_bibdk_webservices');
      }
    }
    if( !empty($cached_result) ) {
      $res = $cached_result->data;
      return $res;
    }
    return FALSE;
  }

  public static function get_all_cache_classes() {
    static $ret;
    if( !isset($ret) ) {
      $ret = array();
      $factory = self::request_factory();
      $request_methods = get_class_methods($factory);
      foreach( $request_methods as $method ) {
        if( $method != '__construct' ) {
          $class = $factory->$method();
          if( is_object($class)) {
            if( self::_class_implements($class,'ITingClientRequestCache') ) {
              $arr['element_name'] = get_class($class);
              $ret[] = $arr;
            }
          }
        }
      }
    }
    return $ret;
  }

  /* \brief check whether a given class implements a specific interface
   *  @param class ; the class to check
   *  @param interface ; the interface to check for
   *  return bool
   */
  private static function _class_implements( $class, $interface ) {
      $interfaces = class_implements($class);
      if( in_array($interface, $interfaces) ) {
        return true;
      }
    return false;
  }

  /**
   * execute a request
   **/
  private function execute($request) {
    $cache = FALSE;
    //if(FALSE){
    if( $res = $this->get_from_cache($request,$cache) ){
      return $res;
    }
    else{
      try {
        timer_start('search_client');
        $res = self::ting_client()->execute($request);
        timer_stop('search_client');
        if( $cache ) {
          //@TODO make request cache table
          $expire = REQUEST_TIME + ( 60*$request->cacheTimeout() );
          cache_set($request->cacheKey() , $res, 'cache_bibdk_webservices', $expire);
        }
        return $res;
      }
      catch (TingClientException $e) {
        timer_stop('search_client');
        // set message if ting-client throws an exeception
        drupal_set_message( $e->getMessage(). '. ' . self::_set_internal_error_message(),'error');
        watchdog('ting client', 'Error performing request: ' . $e->getMessage() , NULL, WATCHDOG_ERROR, 'http://' . $_SERVER["HTTP_HOST"] . request_uri());
        return FALSE;
      }
    }
  }

  private static function _set_internal_error_message() {
    return t('Please contact site administrator if problem persists');
  }

  /* \brief
   * Initialize TingClient.
   * return TingClient. TingClient takes a logger and an adapter in constructor
   * @see ting-client/lib/TingClient.php
   * @see ting-client/lib/adapter/TingClientRequestAdapter.php
   * @see ting-client/lib/log/TingClientDrupalWatchDogLogger.php
   **/
  private static function ting_client() {
    if (!isset(self::$client)) {
      $logger = (variable_get('ting_enable_logging', FALSE)) ? new TingClientDrupalWatchDogLogger(self::request_factory()) : new TingClientVoidLogger();
      self::$ting_client = new TingClient(new TingClientRequestAdapter(), $logger);
    }
    return self::$ting_client;
  }

  /** \brief
   * initialize requestfactory
   * return TingClientRequestFactory (@see ting-client/lib/request/TingClientRequestFactory.php
   **/
  private static function request_factory() {
    if (!isset(self::$request_factory)) {
      $url_variables = array(
        'search' =>  'ting_search_url',
        'scan' => 'ting_scan_url',
        'object' => 'ting_search_url',
        'collection' => 'ting_search_url',
        'spell' => 'ting_spell_url',
        'recommendation' => 'ting_recommendation_url',
      );

      // TODO: This should probably be rethought.
      if (module_exists('ting_infomedia') && variable_get('ting_infomedia_url', FALSE)) {
        $url_variables['infomedia'] = 'ting_infomedia_url';
      }

      if (module_exists('bibdk_vejviser')) {
        $url_variables['agency'] = 'agency_search_url';
      }

      $urls = array();
      foreach ($url_variables as $name => $setting) {
        $urls[$name] = variable_get($setting, FALSE);
        if (!$urls[$name]) {

          // throw new TingClientException('No Ting webservice url defined for ' . $name);
        }
      }
      self::$request_factory = new TingClientRequestFactory($urls);
    }
    return self::$request_factory;
  }


  /**
   * Attempt to quote reserved words in a search query.
   *
   * As proper quoting would require a full CQL parser, we cheat and
   * just work on the part we know is the free text part.
   *
   * Also, we don't mess with uppercase reserved words.
   */
  public static function quote($string) {
    if (preg_match('/^(.*?)(AND \(.*|$)/', $string, $rx)) {
      $keys = $rx[1];
      $new_keys = preg_replace_callback('/(?:(".*?(?<!\\\)")|\b(and|or|not|prox)\b)/i', array('ting_client_class','quote_callback'), $keys);
      $string = preg_replace('/^' . preg_quote($keys). '/', $new_keys, $string);
    }
    return $string;
  }

  /**
   *
   */
  private static function quote_callback($matches) {
    // If $matches[2] is empty, it's because the quote pattern
    // matched. Don't do anything with it.
    if (!empty($matches[2])) {
      // Boolean operator, but not uppercase, quote it.
      if ($matches[2] != drupal_strtoupper($matches[2])) {
        return '"' . $matches[2] . '"';
      }
      // Uppercase boolean operator, return as is.
      return $matches[2];
    }
    // We have a quote. Just return it.
    return $matches[1];
  }
}