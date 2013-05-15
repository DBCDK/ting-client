<?php

/* \brief
 * class depends heavily on ting-client module.
 * builds requests for opensearch, openscan, openspell, openadhl and useraccessinfomedia webservices
 */

class ting_client_class {

  private static $request_factory;
  private static $ting_client;
  private static $enable_cache = FALSE;
  private static $enable_logging = FALSE;
  private static $module;

  public function __construct() {
    // client_class tries to get some settings from the module using it.
    // this is done to clear away drupal specific methods, so this library
    // can be used by other frameworks
    // where is this class used
    // ting_client_class needs to know to get its settings; e.g urls for webservices and cache settings
    // @TODO; get module name from somewhere else
    self::$module = 'ting_client';

    if (function_exists(self::$module . '_enable_cache')) {
      $function = self::$module . '_enable_cache';
      self::$enable_cache = call_user_func($function);
    }

    if (function_exists(self::$module . '_enable_logging')) {
      $function = self::$module . '_enable_logging';
      self::$enable_logging = call_user_func($function);
    }
  }

  public function do_infomedia($params){
    $request = $this->request_factory()->getNamedRequest('infomediaArticle', 'TingClientInfomediaArticleRequest');
    //$request->setXsdNamespace(array(0 => 'http://oss.dbc.dk/ns/openorder'));
    if (empty($request)) {
      $this->set_message('REMEMBER TO IMPLEMENT TingClientInfomediaArticleRequest CLASS BEFORE USING IT', 'error');
      return;
    }
    $this->_set_params($request, $params);
    return $request->parse($this->execute($request));
  }

  /**
   * @param $params
   * @return bool|string | stdClass
   */
  public function do_userstatusrequest($params){
    $request = $this->request_factory()->getNamedRequest('openuserstatus', 'OpenUserstatusRequest');
    $request->setXsdNamespace(array(0 => 'http://oss.dbc.dk/ns/openuserstatus'));
    if (empty($request)) {
      $this->set_message('REMEMBER TO IMPLEMENT ting_openuserstatus CLASS BEFORE USING IT', 'error');
      return;
    }
    $this->_set_params($request, $params);
    return $this->execute($request);
  }

  public function do_navfors($params){
    $request = $this->request_factory()->getNamedRequest('navfors', 'bibdk_navfors');
    //$request->setXsdNamespace(array(0 => 'http://oss.dbc.dk/ns/openorder'));
    if (empty($request)) {
      $this->set_message('REMEMBER TO IMPLEMENT bibdk_navfors CLASS BEFORE USING IT', 'error');
      return;
    }
    $this->_set_params($request, $params);

    return $this->execute($request);
  }

  public function do_adhl($params){
    $request = $this->request_factory()->getNamedRequest('recommendation', 'TingClientObjectRecommendationRequest');
    //$request->setXsdNamespace(array(0 => 'http://oss.dbc.dk/ns/openorder'));
    if (empty($request)) {
      $this->set_message('REMEMBER TO IMPLEMENT bibdk_adhl CLASS BEFORE USING IT', 'error');
      return;
    }
    $this->_set_params($request, $params);
    return $this->execute($request);
  }

  public function do_openorder($params) {
    //$params = ting_client_validate_xsd("http://guesstimate.dbc.dk/~fvs/OpenLibrary/OpenOrder/trunk/openorder.xsd", $params);
    // we know that the bibdk_openorder class exists (if not this method should not be called)
    $request = $this->request_factory()->getNamedRequest('openorder', 'bibdk_openorder');
    $request->setXsdNamespace(array(0 => 'http://oss.dbc.dk/ns/openorder'));
    if (empty($request)) {
      $this->set_message('REMEMBER TO IMPLEMENT bibdk_openorder CLASS BEFORE USING IT', 'error');
      return;
    }
    $this->_set_params($request, $params);
    return $this->execute($request);
  }

  /**
   * execute a borrowercheckrequest
   * */
  public function do_borchk($params) {
    // we know that the bibdk_borchk class exists (if not this method should not be called)
    $request = $this->request_factory()->getNamedRequest('borchk', 'bibdk_borchk');
    if (empty($request)) {
      $this->set_message('REMEMBER TO IMPLEMENT bibdk_borchk CLASS BEFORE USING IT', 'error');
      return;
    }
    $this->_set_params($request, $params);

    return $this->execute($request);
  }

  /**
   * execute a localisationRequest
   * */
  public function do_holdingstatus($params) {
    // for test purposes
    if ($simpletest_prefix = drupal_valid_test_ua()) {
      NanoSOAPClient::setUserAgent(drupal_generate_test_ua($simpletest_prefix));
    }
    // we know that the open_holdingstatus class exists (if not this method should not be called)
    $request = $this->request_factory()->getNamedRequest('holdingstatus', 'open_holdingstatus');
    if (empty($request)) {
      $this->set_message('REMEMBER TO IMPLEMENT open_holdingstatus CLASS BEFORE USING IT', 'error');
      return;
    }
    $this->_set_params($request, $params);

    return $this->execute($request);
  }

  /**
   * getObjectRequest to retreive a single work
   * */
  public function do_get_object($params) {
    $request = $this->request_factory()->getNamedRequest('object', 'TingClientObjectRequest');
    $this->_set_params($request, $params);

    $result = $this->execute($request);
    if (is_object($result)) {
      $result->cacheKey = $request->cacheKey();
    }

    return $result;
  }

  /**
   * initialize a search; call execute
   * method uses a TingClientSearchRequest as parameter
   * */
  public function do_search($params) {
    // get the searchrequest
    $request = $this->request_factory()->getNamedRequest('search', 'TingClientSearchRequest');
    $this->_set_params($request, $params);
    $result = $this->execute($request);

    if (is_object($result)) {
      $result->cacheKey = $request->cacheKey();
    }

    return $result;
  }

  /**
   * \brief general method for requests to openagency
   *
   * @param array $params ; an array of parameters for openagency
   */
  public function do_agency($params) {
    // get the agencyrequest
    $request = $this->request_factory()->getNamedRequest('agency', 'AgencyRequest');
    $request->setXsdNamespace(array(0 => 'http://oss.dbc.dk/ns/openagency'));
    $this->_set_params($request, $params);
    $result = $this->execute($request);

    return $result;
  }

  public function do_agency_search($params) {
    // get the agencyrequest

    $params['action'] = 'findLibraryRequest';
    $result = $this->do_agency($params);

    return $result;
  }

  public function do_agency_service($params) {
    // get the agencyrequest
    $request = $this->request_factory()->getNamedRequest('agency', 'TingClientAgencyServiceRequest');
    $this->_set_params($request, $params);
    $result = $this->execute($request);

    return $result;
  }

  /** \brief
   * initialize requestfactory
   * return TingClientRequestFactory (@see ting-client/lib/request/TingClientRequestFactory.php
   * */
  private function request_factory() {

    //   self::$request_factory=NULL;

    if (!isset(self::$request_factory)) {
      $url_variables = array(
        'agency' => array('url' => 'agency_search_url', 'class' => 'TingClientAgencyRequest'),
        'search' => array('url' => 'ting_search_url', 'class' => 'TingClientSearchRequest'),
        'scan' => array('url' => 'ting_scan_url', 'class' => ''),
        'object' => array('url' => 'ting_search_url', 'class' => 'TingClientObjectRequest'),
        'collection' => array('url' => 'ting_search_url', 'class' => 'TingCollectionRequest'),
        'spell' => array('url' => 'ting_spell_url', 'class' => ''),
        'recommendation' => array('url' => 'ting_recommendation_url', 'class' => ''),
      );

      $urls = array();
      // get the real urls from the module using ting-client
      if (function_exists(self::$module . '_set_request_factory')) {
        $function = self::$module . '_set_request_factory';
        $urls = call_user_func_array($function, array($url_variables));
      }


      self::$request_factory = new TingClientRequestFactory($urls);
    }
    return self::$request_factory;
  }

  /**
   * helper method to set parameters on a TingClientRequest (or extending) object
   * */
  private function _set_params($request, $params) {
    foreach ($params as $key => $value) {
      $setter = 'set' . ucfirst($key);
      if (method_exists($request, $setter)) {
        $request->$setter($value);
        // pjo testing ... value is set twice; delete this line
        // $request->setParameter($key, $value);
      }
      else {
        $message = get_class($request) . ' does not implement: ' . $setter;
        $status = 'error';
        $this->set_message($message, $status);
      }
    }
  }

  private function get_from_cache($request, &$cache) {
    if (!function_exists(self::$module . '_cache_get')) {
      return FALSE;
    }
    else {
      $cache_function = self::$module . '_cache_get';
    }

    // overall caching variable (for all requests)
    $requestcache = self::$enable_cache;
    if ($requestcache && self::_class_implements($request, 'ITingClientRequestCache')) {
      if ($request->cacheEnable()) {
        // everything is okay for caching; set passed cache variable for execute method
        $cache = TRUE;
        // call getRequest method to set parameters for cachekey
        $request->getRequest();

        $cached_result = call_user_func_array($cache_function, array($request->cacheKey(), $request->cacheBin()));
      }
    }
    if (!empty($cached_result)) {
      $res = $cached_result->data;
      return $res;
    }
    return FALSE;
  }

  public function get_all_cache_classes() {
    static $ret;
    if (!isset($ret)) {
      $ret = array();
      $factory = $this->request_factory();

      $request_methods = get_class_methods($factory);
      foreach ($factory->urls as $name => $settings) {
        if (!empty($settings['class'])) {
          if (class_exists($settings['class']) && self::_class_implements($settings['class'], 'ITingClientRequestCache')) {
            // do not handle extending classes
            // disabled for handling infomediaRequests.
            // DO NOT DELETE this might come in handy later on
            // $parents = class_parents($settings['class']);
            //if (count($parents) == 1) {
            $arr['name'] = $name;
            $arr['className'] = $settings['class'];
            $ret[] = $arr;
            // }
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

  private static function _class_implements($class, $interface) {
    $interfaces = class_implements($class);
    if (in_array($interface, $interfaces)) {
      return true;
    }
    return false;
  }

  /**
   * execute a request
   * */
  private function execute($request) {

    // set useragent for simpletest framework
    if ($simpletest_prefix = drupal_valid_test_ua()) {
      NanoSOAPClient::setUserAgent(drupal_generate_test_ua($simpletest_prefix));
    }

    $cache = FALSE;
    if ($res = $this->get_from_cache($request, $cache)) {
      return $res;
    }
    else {

      try {
        // Start timer
        self::_invoke('timer', array('start', 'search_client'));
        // make request
        $res = self::ting_client()->execute($request);
        // stop timer
        self::_invoke('timer', array('stop', 'search_client'));

        if ($cache) {
          $expire = REQUEST_TIME + (60 * $request->cacheTimeout());
          if (function_exists(self::$module . '_cache_set')) {
            $function = self::$module . '_cache_set';
            call_user_func_array($function, array($request->cacheKey(), $res, $request->cacheBin(), $expire));
          }
        }
        return $res;
      }
      catch (Exception $e) {
        self::_invoke('timer', array('stop', 'search_client'));
        $this->_handleRequestExceptions($e);
        return FALSE;
      }
    }
  }

  private function _handleRequestExceptions($e) {
    // set message if ting-client throws an exeception
    $message = "The service you are trying to use in currently unavailable";
    $status = 'error';
    $watchdog['message'] = 'Error performing request: ' . $e->getMessage();
    $watchdog['variables'] = NULL;
    $watchdog['link'] = $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
    self::_invoke('set_message', array($message, $status, $watchdog));

  }

  private static function _invoke($function, $params) {
    if (empty(self::$module)) {
      return;
    }
    $function = self::$module . '_' . $function;
    if (function_exists($function)) {
      call_user_func_array($function, $params);
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
   * */

  // @TODO; get TingClientDrupalWatchDogLogger out of this class
  private static function ting_client() {
    if (!isset(self::$client)) {
      // $logger = (variable_get('ting_enable_logging', FALSE)) ? new TingClientDrupalWatchDogLogger($this->request_factory()) : new TingClientVoidLogger();
      $logger = (self::$enable_logging) ? new TingClientDrupalWatchDogLogger(self::request_factory()) : new TingClientVoidLogger();
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
      $new_keys = preg_replace_callback('/(?:(".*?(?<!\\\)")|\b(and|or|not|prox)\b)/i', array('ting_client_class', 'quote_callback'), $keys);
      $string = preg_replace('/^' . preg_quote($keys) . '/', $new_keys, $string);
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
