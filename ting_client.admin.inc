<?php

/**
 * return a search_client configuration form :
 * set name on search tab
 * disable/enable cache
 **/
function ting_client_cache_admin_form($form_state) {
  $form = array();

  $form['openformat']['search_client_enable_cache'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable cache'),
    '#description' => t('overall caching - very nice while testing, but might stress the system in production.
    disable this if you wish NO caching for any webservices'),
    '#default_value' => variable_get('search_client_enable_cache', FALSE),
  );


  $client = new ting_client_class();
  $cache_classes =  $client->get_all_cache_classes();
  // suffixes to use for variables
  $cache_lifetime = TingClientRequest::cache_lifetime;
  $cache_enable = TingClientRequest::cache_enable;

  // make cache configuration foreach request that implements ITingClientRequestCache
  foreach( $cache_classes as $cache_class ) {
    $element_name = $cache_class['name'];
    $class_name = $cache_class['className'];
    $form['openformat'][$element_name] = array(
      '#type' => 'fieldset',
      '#title' => $element_name,
    );

    $form['openformat'][$element_name][$class_name.$cache_enable] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable cache'),
      '#description' => t('Enable cache for this type of request' ),
      '#default_value' => variable_get($class_name.$cache_enable, FALSE),
    );

    $form['openformat'][$element_name][$class_name.$cache_lifetime] = array(
      '#type' => 'textfield',
      '#title' => t('Cache lifetime'),
      '#description' => t('For how many minutes do you wish to cache results'),
      '#default_value' => variable_get($class_name.$cache_lifetime,'10'),
    );
  }

  $form['#submit'] = array('ting_client_cache_admin_form_submit');

  $form['buttons']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save changes'),
  );

  return $form;
}

/**
 * submit handler for search_client_admin_form
 **/
function ting_client_cache_admin_form_submit($form, $form_state) {
$client = new ting_client_class();
$cache_classes =  $client->get_all_cache_classes();
//  $cache_classes =  ting_client_class::get_all_cache_classes();

  // suffixes for variables
  $cache_lifetime = TingClientRequest::cache_lifetime;
  $cache_enable = TingClientRequest::cache_enable;

  foreach( $form_state['values'] as $key => $val ) {
    if( strpos($key, $cache_lifetime) || strpos($key, $cache_enable ) ) {
      variable_set($key, $val);
    }
  }

  // overall caching 
  variable_set('search_client_enable_cache',  $form_state['values']['search_client_enable_cache']);
  // rebuild menu to show search_client_name on search-tab
  menu_rebuild();
  drupal_set_message(t('The changes has been saved'));
}


/**
 * Form builder; Configure ting settings for this site.
 *
 * @ingroup forms
 * @see system_settings_form()
 */
function ting_client_admin_webservices_settings($form_state) {
  form_load_include($form_state, 'inc', 'ting', 'ting_client.admin');
  $form = array();

  $form['ting'] = array(
    '#type' => 'fieldset',
    '#title' => t('client webservice settings'),
    '#tree' => FALSE,
  );

  $form['webservices']['ting_agency'] = array(
    '#type' => 'textfield',
    '#title' => t('Library code'),
    '#description' => t('The 6-digit code representing the library organization'),
    '#default_value' => variable_get('ting_agency', ''),
  );

  $form['webservices']['ting_search_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Search service URL'),
    '#description' => t('URL to the Ting search webservice, e.g. http://bibliotek.dk/openbibdk/next/'),
    '#required' => TRUE,
    '#default_value' => variable_get('ting_search_url', ''),
  );

  $form['webservices']['ting_scan_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Scan service URL'),
    '#description' => t('URL to the Ting scan webservice, e.g. http://openscan.addi.dk/1.7/'),
    '#required' => TRUE,
    '#default_value' => variable_get('ting_scan_url', ''),
  );

  $form['webservices']['ting_spell_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Spell service URL'),
    '#description' => t('URL to the Ting spell webservice, e.g. http://openspell.addi.dk/1.2/'),
    '#required' => TRUE,
    '#default_value' => variable_get('ting_spell_url', ''),
  );

  $form['webservices']['ting_recommendation_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Recommendation service URL'),
    '#description' => t('URL to the Ting recommendation webservice (Andre der har lånt...) , e.g. http://openadhl.addi.dk/1.1/'),
    '#required' => TRUE,
    '#default_value' => variable_get('ting_recommendation_url', ''),
  );

  $form['webservices']['ting_enable_logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable logging'),
    '#default_value' => variable_get('ting_enable_logging', FALSE),
    '#weight' => 1,
  );

  $intervals = array(
    60,
    300,
    900,
    1800,
    3600,
    10800,
    43200,
    86400,
    259200,
    604800,
  );
  $options = array(
    0 => t('No caching'),
  );

  foreach ($intervals as $interval) {
    $options[$interval] = format_interval($interval, 2);
  }

  $form['webservices']['ting_cache_lifetime'] = array(
    '#type' => 'select',
    '#title' => t('Cache lifetime'),
    '#default_value' => variable_get('ting_cache_lifetime', TING_DEFAULT_CACHE_LIFETIME),
    '#options' => $options,
    '#description' => t("Cache answers from the datawell for this time. At least a few minutes is recommended, so the same objects doesn't have to be fetched on each page request."),
    '#weight' => 1,
  );

  $form['webservices']['ting_search_profile'] = array(
    '#type' => 'textfield',
    '#title' => t('Search profile'),
    '#description' => t('Profile name, used for filtering the search based on settings from vip.dbc.dk.'),
    '#default_value' => variable_get('ting_search_profile', ''),
  );

  $form['webservices']['ting_search_openformat'] = array(
    '#type' => 'textfield',
    '#title' => t('OpenFormat profile'),
    '#description' => t('OpenFormat format name. Leave blank if using the default search result format (dkabm).'),
    '#default_value' => variable_get('ting_search_openformat', 'dkabm'),
  );

  return system_settings_form($form);
}
