<?php
/**
 * controller控制类
 */
abstract class sys_model
{

	protected $db = '';
	protected $prifix;

	function __construct()
	{

	}

	function write_cache()
	{

	}

	function read_cache()
	{

	}

	function db()
	{
		if(!$this->db)
		{
			$config = sys_config::Get('database');
			$host = $config['master']['host'];		//mysql 主机
			$dbname = $config['master']['dbname'];	//mysql 数据库名
			$uname = $config['master']['uname'];	//mysql 用户名
			$upwd = $config['master']['upwd'];		//mysql 密码
			$encode = $config['master']['encode'];	//mysql 编码
			$prifix = $config['master']['prifix'];	//mysql 表前缀
			$this->prifix = $prifix;
			$this->db = new sys_database($host,$uname,$upwd,$dbname,$encode,$config['debug'], $config['errReport']);
		}
		return $this->db;
	}
}
?>
