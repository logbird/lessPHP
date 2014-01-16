<?php
class My_Model extends sys_model
{
	public static $_redis;
	protected $db = array();

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

    /**
     * 要链接的数据库
     *
     * @param mixed $db
     * @access public
     * @return void
     */
	public function db($db = 'main')
	{
		if(!isset($this->db[$db]) || !$this->db[$db])
		{
			$config = sys_config::Get($db, 'db');
            $this->db[$db] = new sys_pdodb($config, $config['debug'], $config['errReport']);
		}
		return $this->db[$db];
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
