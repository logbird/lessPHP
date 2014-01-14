<?php
class plugin_Redis extends Redis
{
	
	public function __construct($host, $port)
	{
		parent::__construct();
		$this->connect($host, $port);
	}

	public function setex($key, $expirs, $value)
	{
		return parent::setex($key, $expirs, serialize($value));
	}

	public function set($key, $value)
	{
		return parent::set($key, serialize($value));
	}

	public function get($key)
	{
		$data = parent::get($key);
		return unserialize($data);
	}
}