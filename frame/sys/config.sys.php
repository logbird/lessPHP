<?php
/**
 * 配置文件获取类
 */
class sys_config
{

	static function Get($key)
	{
		$config = $GLOBALS['config'];
		$config = isset($config[$key]) ? $GLOBALS['config'][$key] : '';
		return $config;
	}

    static function Set($key, $value)
	{
		$config = $GLOBALS['config'];
		$GLOBALS['config'][$key] = $value;
	}
}
?>
