<?php

require_once dirname(__FILE__) . '/../vendor/simpletest/autorun.php';
require_once dirname(__FILE__) . '/../lib/adapter/http/TingClientZfHttpRequestAdapter.php';
require_once dirname(__FILE__) . '/../lib/request/rest-json/RestJsonTingClientRequestFactory.php';
require_once dirname(__FILE__) . '/../lib/TingClient.php';
require_once dirname(__FILE__) . '/../lib/log/TingClientSimpleTestLogger.php';

require_once 'Zend/Http/Client.php';

class TingClientLiveZfTest extends UnitTestCase {
	
	private $searchUrl = 'http://didicas.dbc.dk/opensearch_0.7/';
	private $scanUrl = 'http://didicas.dbc.dk/openscan/server.php';
	private $recommendationUrl = 'http://didicas.dbc.dk/openadhl/server.php';
	private $spellUrl = 'http://didicas.dbc.dk/openspell/server.php';
	
	/**
	 * @var TingClient
	 */
	protected $client;
	
	/**
	 * @var TingClientRequestFactory
	 */
	protected $requestFactory;
	
	function __construct()
	{
		$this->requestFactory = new RestJsonTingClientRequestFactory(
																array('search' => $this->searchUrl,
																			'scan' => $this->scanUrl,
																			'collection' => $this->searchUrl,
																			'object' => $this->searchUrl,
																			'recommendation' => $this->recommendationUrl,
																			'spell' => $this->spellUrl));
				
		$requestAdapter = new TingClientZfHttpRequestAdapter(new Zend_Http_Client());

		$this->client = new TingClient(	$requestAdapter,
																		new TingClientSimpleTestLogger($this));
		
	}

}
