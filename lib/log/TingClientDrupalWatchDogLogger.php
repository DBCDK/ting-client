<?php

/**
 * Ting logger wrapper for the Drupal watchdog API.
 *
 * @see http://api.drupal.org/api/function/watchdog/
 */
class TingClientDrupalWatchDogLogger extends TingClientLogger {
  public function doLog($message, $variables, $severity) {
    watchdog('ting client',$message, $variables,
             constant('WATCHDOG_' . $severity),
             'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  }
}

