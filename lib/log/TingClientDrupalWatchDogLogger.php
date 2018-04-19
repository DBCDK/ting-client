<?php

/**
 * Ting logger wrapper for the Drupal watchdog API.
 *
 * @see http://api.drupal.org/api/function/watchdog/
 */
class TingClientDrupalWatchDogLogger extends TingClientLogger {
  public function doLog($message_type, $variables, $severity) {

    $variables['time'] = $this->log_time;
    $vars = array();
    foreach ($variables as $key => $value){
      $vars['@'.$key] = $value;
    }
    switch($message_type){
      case 'soap_request_complete':
        $message = 'Completed @adapter request @action @wsdlUrl ( @time s). Request body: @requestBody';
        if (!empty($variables['requestVariables'])) {
          $message .= '. Request variables: @requestVariables';
        }
        break;
      case 'soap_request_error':
        $message = 'Error handling @adapter request @action @wsdlUrl: @error';
        break;
      default :
        $vars['@type'] = $message_type;
        $message = '@type request @action @wsdlUrl ( @time s). Request body: @requestBody';
    }

    watchdog('ting client',$message, $variables,
             constant('WATCHDOG_' . $severity),
             'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  }
}

