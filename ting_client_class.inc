<?php

/* \brief
 * class depends heavily on ting-client module.
 * builds requests for opensearch, openscan, openspell, openadhl and useraccessinfomedia webservices
 */

class ting_client_class {
  private static $request_factory;
  private static $ting_client;
  private static $enable_cache=FALSE;
  private static $module;

  public function __construct () {
    // client_class tries to get some settings from the module using it.
    // this is done to clear away drupal specific methods, so this library
    // can be used by other frameworks

    // where is this class used
    // ting_client_class needs to know to get its settings; e.g urls for webservices and cache settings
    // @TODO; get module name from somewhere else
    self::$module = 'ting_client';

    if( function_exists(self::$module.'_enable_cache') ) {
      $function = self::$module.'_enable_cache';
      self::$enable_cache = call_user_func($function);
    }
  }

  public function do_openorder($params){
    //$params = ting_client_validate_xsd("http://guesstimate.dbc.dk/~fvs/OpenLibrary/OpenOrder/trunk/openorder.xsd", $params);
    // we know that the bibdk_openorder class exists (if not this method should not be called)
    $request = $this->request_factory()->getNamedRequest('openorder','bibdk_openorder');
    $request->setXsdNamespace(array(0 => 'http://oss.dbc.dk/ns/openorder'));
    if( empty($request) ) {
      $this->set_message('REMEMBER TO IMPLEMENT bibdk_openorder CLASS BEFORE USING IT', 'error');
      return;
    }
    $this->_set_params($request,$params);
    return $this->execute($request);
  }

  /**
   * execute a borrowercheckrequest
   **/
  public function do_borchk($params) {
    // we know that the bibdk_borchk class exists (if not this method should not be called)
    $request = $this->request_factory()->getNamedRequest('borchk','bibdk_borchk');
    if( empty($request) ) {
      $this->set_message('REMEMBER TO IMPLEMENT bibdk_borchk CLASS BEFORE USING IT', 'error');
      return;
    }
    $this->_set_params($request,$params);
    
    return $this->execute($request);
  }

  /**
   * getObjectRequest to retreive a single work
   **/
  public function do_get_object($params) {
    $request = $this->request_factory()->getNamedRequest('object','TingClientObjectRequest');
    $this->_set_params($request,$params);
    $result = $this->execute($request);
    $result->cacheKey = $request->cacheKey();
    return $result;
  }

  /**
   * initialize a search; call execute
   * method uses a TingClientSearchRequest as parameter
   **/
  public function do_search($params) {
    // get the searchrequest
    $request = $this->request_factory()->getNamedRequest('search','TingClientSearchRequest');
    $this->_set_params($request,$params);
    $result = $this->execute($request);

    // pass cache key to search
    $result->cacheKey = $request->cacheKey();
    return $result;
  }
  
  /**
   * \brief general method for requests to openagency
   * 
   * @param array $params ; an array of parameters for openagency
   */
  public function do_agency($params) {
    // get the agencyrequest
    $request = $this->request_factory()->getNamedRequest('agency','AgencyRequest');
    $request->setXsdNamespace(array(0 => 'http://oss.dbc.dk/ns/openagency'));
    $this->_set_params($request,$params);
    $result = $this->execute($request);

    return $result;
  }
  

  public function do_agency_search($params){
    // get the agencyrequest 
    
    $params['action'] = 'findLibraryRequest';
    $result = $this->do_agency($params);
    
    return $result;
  }

  public function do_agency_service($params){
    // get the agencyrequest
    $request = $this->request_factory()->getNamedRequest('agency','TingClientAgencyServiceRequest');
    $this->_set_params($request,$params);
    $result = $this->execute($request);

    return $result;
  }

   /** \brief
   * initialize requestfactory
   * return TingClientRequestFactory (@see ting-client/lib/request/TingClientRequestFactory.php
   **/
  private function request_factory() {

 //   self::$request_factory=NULL;

    if (!isset(self::$request_factory)) {
      $url_variables = array(
        'agency' =>  array('url'=>'agency_search_url','class'=>'TingClientAgencyRequest'),
        'search' =>  array('url'=>'ting_search_url','class'=>'TingClientSearchRequest'),
        'scan' => array('url'=>'ting_scan_url','class'=>''),
        'object' => array('url'=>'ting_search_url','class'=>'TingClientObjectRequest'),
        'collection' => array('url'=>'ting_search_url','class'=>'TingClientSearchRequest'),
        'spell' => array('url'=>'ting_spell_url','class'=>''),
        'recommendation' => array('url'=>'ting_recommendation_url','class'=>''),
      );

      $urls = array();
      // get the real urls from the module using ting-client
      if( function_exists(self::$module.'_set_request_factory') ) {
        $function = self::$module.'_set_request_factory';
        $urls = call_user_func_array($function,array($url_variables));
      }


      self::$request_factory = new TingClientRequestFactory($urls);
    }
    return self::$request_factory;
  }

  /**
   * ting_client_class should be kept clean of drupal specific methods etc.
   * wrapper for $module_set_message
   * @TODO .. some default implementation of setting a message (die($message) or something)
   **/
  private function set_message ($message,$type = 'status',$repeat=NULL) {
    if( !empty(self::$module) ) {
      if (function_exists(self::$module.'_set_message')) {
        $function = self::$module.'_set_message';
        $func_args = func_get_args();
        call_user_func_array($function, $func_args);
      }
    }
  }

  /**
   * helper method to set parameters on a TingClientRequest (or extending) object
   **/
  private function _set_params($request,$params) {
    foreach ($params as $key => $value) {
      $setter = 'set' . ucfirst($key);
      if( method_exists( $request,$setter ) ){
        $request->$setter($value);
      }
      else {
        $message = get_class($request).' does not implement: '.$setter;
        $status = 'error';
        $this->set_message ($message,$status);
      }
    }
  }


  private function get_from_cache($request,&$cache) {
    if( !function_exists(self::$module.'_cache_get') ) {
      return FALSE;
    }
    else {
      $cache_function = self::$module.'_cache_get';
    }

    // overall caching variable (for all requests)
    $requestcache = self::$enable_cache;
    if( $requestcache && self::_class_implements( $request, 'ITingClientRequestCache' ) ) {
      if($request->cacheEnable()){
        // everything is okay for caching; set passed cache variable for execute method
        $cache = TRUE;
        //        $cached_result = cache_get($request->cacheKey(),'cache_bibdk_webservices');
        $cached_result = call_user_func_array($cache_function, array($request->cacheKey(),'cache_bibdk_webservices'));
      }
    }
    if( !empty($cached_result) ) {
      $res = $cached_result->data;
      return $res;
    }
    return FALSE;
  }

  public function get_all_cache_classes() {
    static $ret;
    if( !isset($ret) ) {
      $ret = array();
      $factory = $this->request_factory();
      $request_methods = get_class_methods($factory);
      /*reach( $request_methods as $method ) {
        if( $method != '__construct' ) {
          $class = $factory->$method();
          if( is_object($class)) {
            if( self::_class_implements($class,'ITingClientRequestCache') ) {
              $arr['element_name'] = get_class($class);
              $ret[] = $arr;
            }
          }
        }
        }*/
      foreach( $factory->urls as $name=>$settings ) {
        if( !empty($settings['class']) ) {
          if( class_exists($settings['class']) && self::_class_implements($settings['class'],'ITingClientRequestCache') ) {
            $arr['name'] = $name;
            $arr['className'] = $settings['class'];
            $ret[] = $arr;
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
        if( function_exists(self::$module.'_timer') ) {
          $function = self::$module.'_timer';
          call_user_func_array ($function, array('start', 'search_client'));
          //          timer_start('search_client');
        }
        $res = self::ting_client()->execute($request);
        if( function_exists(self::$module.'_timer') ) {
          $function = self::$module.'_timer';
          call_user_func_array ($function, array('stop', 'search_client'));
          //          timer_start('search_client');
        }
        //timer_stop('search_client');
        if( $cache ) {
          //@TODO make request cache table
          $expire = REQUEST_TIME + ( 60*$request->cacheTimeout() );
          // cache_set($request->cacheKey() , $res, 'cache_bibdk_webservices', $expire);
          if( function_exists(self::$module.'_cache_set') ) {
            $function = self::$module.'_cache_set';
            call_user_func_array ($function, array($request->cacheKey() , $res, 'cache_bibdk_webservices', $expire));
          }
        }
        return $res;
      }
      catch (TingClientException $e) {
        if( function_exists(self::$module.'_timer') ) {
          $function = self::$module.'_timer';
          call_user_func_array ($function, array('stop', 'search_client'));
        }
        // timer_stop('search_client');
        // set message if ting-client throws an exeception
        $message = $e->getMessage(). '. ' . self::_set_internal_error_message();
        $status = 'error';
        $watchdog['message'] = 'Error performing request: ' . $e->getMessage();
        $watchdog['variables'] = NULL;
        $watchdog['link'] = $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
        $this->set_message($message,$status,$watchdog);
        //drupal_set_message( $e->getMessage(). '. ' . self::_set_internal_error_message(),'error');
        //watchdog('ting client', 'Error performing request: ' . $e->getMessage() , NULL, WATCHDOG_ERROR, 'http://' . $_SERVER["HTTP_HOST"] . request_uri());
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

  // @TODO; get TingClientDrupalWatchDogLogger out of this class
  private static function ting_client() {
    if (!isset(self::$client)) {
      // $logger = (variable_get('ting_enable_logging', FALSE)) ? new TingClientDrupalWatchDogLogger($this->request_factory()) : new TingClientVoidLogger();
      $logger = (self::$enable_cache) ? new TingClientDrupalWatchDogLogger($this->request_factory()) : new TingClientVoidLogger();
      self::$ting_client = new TingClient(new TingClientRequestAdapter(), $logger);
    }
    return self::$ting_client;
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
