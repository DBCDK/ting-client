<?php
/**
 * @file
 * Hooks provided by the ting-client module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * add a webservice request to ting-client
 * @return array of request settings
 * 
 * REQUIRED SETTINGS
 *   - class: name of the class extending TingClientRequest (@see lib/request/TingClientRequest.php)
 *   - url: name of variable holding the url of the webservice
 * OPTIONAL SETTINGS
 *   - custom_parse: name of the method to parse the response
 *   - xsdNamespace: array of namespaces to add to the request
 **/
function hook_ting_client_webservice() {
  $ret = array();
  // REQUIRED
  $ret['holdingstatus']['class'] = 'open_holdingstatus';
  $ret['holdingstatus']['url'] = 'open_holdingstatus_url';
  // OPTIONAL
  $ret['holdingstatus']['xsdNamespace'] = array(0 => 'http://oss.dbc.dk/ns/openagency');
  $ret['holdingstatus']['custom_parse'] = 'parse_me';
  return $ret;
}