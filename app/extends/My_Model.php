<?php
class My_Model extends sys_model
{
	public static $_redis;
	protected $db = '';

	public function __construct()
	{
		parent::__construct();
	}

	public static function redis()
	{
		if (!self::$_redis) {
			$config = sys_config::Get('redis');
			self::$_redis = new plugin_Redis($config['host'], $config['port']);
		}
		return self::$_redis;
	}

	public function db()
	{
		if(!$this->db)
		{
			$config = sys_config::Get('database');
            $this->db = new sys_pdodb($config, $config['debug'], $config['errReport']);
		}
		return $this->db;
	}

	public function startTrans()
	{
		$this->db()->startTrans();
	}

	public function rollbackTrans()
	{
		$this->db()->rollbackTrans();
	}

	public function commitTrans()
	{
		$this->db()->commitTrans();
	}
}
