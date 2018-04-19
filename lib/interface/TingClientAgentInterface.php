<?php

/**
 * @file  Interface TingClientAgentInterface
 *
 * Interface for clients used in TingClientRequestAdapter
 * @see lib/adapter/TingClientRequestAdapter
 *
 */
interface TingClientAgentInterface {
  /**
   * Make a SOAP request.
   *
   * @param string $action
   *   The SOAP action to perform/call.
   * @param array  $parameters
   *   The parameters to send with the SOAP request.
   *
   * @return string
   *   The SOAP response.
   */
  public function call($action, $params);

  /**
   * Get a status for the request performed. For backwards compatibility
   * this method has a weird name (curl).
   *
   * @return array
   *  An array with status (as in curl_getinfo)
   * @see http://php.net/manual/en/function.curl-getinfo.php
   */
  public function getCurlInfo();

  /**
   * Return requestBodyString (for logging request).
   *
   * @return string
   */
  public function getRequestBodyString();


  /**
   * Return getRequestVariables (for logging request).
   *
   * @return string
   */
  public function getRequestVariables();


  /**
   * Return getRequestAdapter (for logging request).
   *
   * @return string
   */
  public function getRequestAdapter();
}