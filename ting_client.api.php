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
 *   - xsd_url: name of variable holding the url of the webservice's XSD
 **/
function hook_ting_client_webservice() {
  $ret = array();
  // REQUIRED
  $ret['openHoldingStatus']['class'] = 'openHoldingStatus';
  $ret['openHoldingStatus']['url'] = 'openHoldingStatus_url';
  // OPTIONAL
  $ret['openHoldingStatus']['xsdNamespace'] = array(0 => 'http://oss.dbc.dk/ns/openagency');
  $ret['openHoldingStatus']['custom_parse'] = 'parse_me';
  return $ret;
}