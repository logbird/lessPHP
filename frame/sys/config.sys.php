<?php
/**
 * 配置文件获取类 
 * 
 * @package 
 * @version $id$
 * @author logbird <logbird@126.com> 
 */
class sys_config
{
    /**
     * 存放全局的配置文件
     * 
     * @static
     * @var array
     * @access public
     */
    static $config = array();

    /**
     * 根据KEY 后去配置文件值
     * 
     * @param mixed $key 
     * @static
     * @access public
     * @return void
     */
	static function Get($key, $confName = '_main')
	{
		$config = self::$config[$confName];
		$config = isset($config[$key]) ? $config[$key] : '';
		return $config;
	}

    /**
     * 修改config内容
     * 
     * @param mixed $key 
     * @param mixed $value 
     * @static
     * @access public
     * @return void
     */
    static function Set($key, $value, $confName = '_main')
	{
		self::$config[$confName][$key] = $value;
	}

    /**
     * 初始化config信息
     * 
     * @param mixed $config 
     * @static
     * @access public
     * @return void
     */
    static function Init($config, $confName = '_main')
    {
        self::$config[$confName] = $config;
    }
}
?>
