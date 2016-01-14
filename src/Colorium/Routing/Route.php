<?php

namespace Colorium\Routing;

class Route
{

	/** @var string http verb (ANY, GET, POST, PUT, DELETE, OPTIONS, HEAD) */
	public $method = 'ANY';

	/** @var string http uri (/foo/:bar) */
	public $uri = '/';

	/** @var string http uri regex (#^\/foo\/(?P<bar>\w+)$#) */
	public $compiled;

	/** @var array additional data (lang => en) */
	public $meta = [];

	/** @var callable resource to execute (\App\Foo::bar) */
	public $resource;

	/** @var array params extracted from input uri using regex (bar => 42) */
	public $params = [];


	/**
	 * Create route
	 *s
	 * @param string $method
	 * @param string $uri
	 * @param callable $resource
	 * @param array $meta
	 */
	public function __construct($method, $uri, $resource, array $meta = [])
	{
		$this->method = strtoupper($method);
		$this->uri = $uri;
		$this->resource = $resource;
		$this->meta = $meta;

		$this->compiled = $uri;
	}

}