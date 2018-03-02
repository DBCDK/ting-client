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
  private static $searchKey;

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

  /**
   * @param $requestName
   * @param $params
   * @param $cache_me; override configuration cache settings if needed
   * @return bool|stdClass|string
   *
   */
  public function do_request($requestName, $params, $cache_me=TRUE) {
    // set appropiate agent for testing
    if ($simpletest_prefix = drupal_valid_test_ua()) {
      NanoSOAPClient::setUserAgent(drupal_generate_test_ua($simpletest_prefix));
    }

    $settings = $this->request_factory()->getSettings($requestName);

    $request = $this->request_factory()->getNamedRequest($requestName, $settings['class']);

    if (empty($request)) {
      $this->set_message('REMEMBER TO IMPLEMENT ' . $requestName . ' CLASS BEFORE USING IT', 'error');
      return;
    }
    // set namespace(s) if set in settings
    if (isset($settings['xsdNamespace'])) {
      $request->setXsdNamespace($settings['xsdNamespace']);
    }

    $this->_set_params($request, $params);

    $result = $this->execute($request, $cache_me);
    // add cachekey to result
    if (is_object($result)) {
      if (method_exists($request, 'cacheKey')) {
        $result->cacheKey = $request->cacheKey();
      }
      if (method_exists($request, 'searchKey')) {
        $result->searchKey = $request->searchKey();
      }
    }

    if (isset($settings['custom_parse'])) {
      if (method_exists($request, $settings['custom_parse'])) {
        $function = $settings['custom_parse'];
        return $request->$function($result);
      }
    }

    return $result;
  }


  /**
   * @return array.
   * List of variable names for XSD URLs. Fex. array('holdingstatus'=>'open_holdingstatus_xsd_url')
   */
  public function getXSDurls() {
    return $this->request_factory()->getXSDurls();
  }


  /**
   * Get service definitions
   *
   * @return array.
   * List of variable webservices
   */
  public function getUrls() {
    return $this->request_factory()->getUrls();
  }



  /** \brief
   * initialize requestfactory
   * return TingClientRequestFactory (@see ting-client/lib/request/TingClientRequestFactory.php
   * */
  private function request_factory() {

    //   self::$request_factory=NULL;

    if (!isset(self::$request_factory)) {
      $url_variables = array(
        'search' => array('url' => 'ting_search_url', 'class' => 'TingClientSearchRequest'),
        // 'scan' => array('url' => 'ting_scan_url', 'class' => ''),
        'object' => array('url' => 'ting_search_url', 'class' => 'TingClientObjectRequest'),
        'collection' => array('url' => 'ting_search_url', 'class' => 'TingCollectionRequest'),
        // 'spell' => array('url' => 'ting_spell_url', 'class' => ''),
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
      }
      else {
        $message = get_class($request) . ' does not implement: ' . $setter;

        $error['message'] = $message;
        $error['status'] = 'error';
        $error['repeat'] = FALSE;
        $error['watchdog'] = array(
          'type' => 'error',
          'message' => $message,
          'link' => '',
        );

        self::_invoke('set_message', $error);
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
  private function execute($request, $cache_me = TRUE) {

    // set useragent for simpletest framework
    if ($simpletest_prefix = drupal_valid_test_ua()) {
      NanoSOAPClient::setUserAgent(drupal_generate_test_ua($simpletest_prefix));
    }

    $cache = FALSE;
    if (($res = $this->get_from_cache($request, $cache)) && $cache_me) {
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

        /** @var TingClientRequest $request */
        if($request->checkResponse($res)) {
          if ($cache) {
            $expire = REQUEST_TIME + (60 * $request->cacheTimeout());
            if (function_exists(self::$module . '_cache_set')) {
              $function = self::$module . '_cache_set';
              call_user_func_array($function, array($request->cacheKey(), $res, $request->cacheBin(), $expire));
            }
          }
        }
        else{
          watchdog('ting client', '%class returned FALSE in checkResponse NO caching done', array('%class' => get_class($request)), WATCHDOG_ERROR);
        }
        return $res;
      } catch (Exception $e) {
        self::_invoke('timer', array('stop', 'search_client'));
        $this->_handleRequestExceptions($e, $request->getWsdlUrl());
        return FALSE;
      }
    }
  }

  private function _handleRequestExceptions($e, $url) {
    // set message if ting-client throws an exeception
    $message = t('something_went_wrong', array(), array('context' => 'ting_client'));
    $status = 'error';
    $watchdog['type'] = 'ting_client';
    $watchdog['message'] = 'Error performing request: ' . $e->getMessage();
    if ( !empty($url) ) {
      $watchdog['message'] .= '. URL: ' . $url;
    }
    $watchdog['variables'] = NULL;
    $watchdog['status'] = $status;
    $watchdog['link'] = $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
    self::_invoke('set_message', array($message, 'error', NULL, $watchdog));
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

  public static function getAllUrls() {
    return self::request_factory()->getUrls();
  }

}
