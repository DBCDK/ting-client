<?php

/**
 * Ting logger wrapper for the Drupal watchdog API.
 *
 * @see http://api.drupal.org/api/function/watchdog/
 */
class TingClientDrupalWatchDogLogger extends TingClientLogger
{
  public function doLog($message_type, $variables, $severity)
  {
    $log_type = 'ting client';
    // we want a seperate log type for slow requests
    if ($this->log_time > 2.0) {
      $log_type = 'SLA alert';
    }
    $variables['time'] = $this->log_time;
    switch ($message_type) {
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
        $message = '@type request @action @wsdlUrl ( @time s). Request body: @requestBody';
    }

    watchdog($log_type, $message, $variables,
      constant('WATCHDOG_' . $severity),
      'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  }
}

