<?php
/**
 * controller控制类
 */
abstract class sys_controller
{
	protected $backurl = '';

	function __construct($tmpl = '')
	{
		//开启input过滤
		$this->request = new sys_request();
		$this->backurl = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:'/';
	}

	//跳转页面
	function direct($url)
	{
		commonPlugin::Direct($url);
	}

	/**
	 * 显示错误信息
	 * @param msg string 错误提示信息
	 * @param url string 跳转页面的链接
	 * @param msg string 自动跳转时间 如果是0则为不自动跳转
	 **/
	function showMsg($msg = '出错啦。。。', $url = '', $second = 0)
	{
		$url = empty($url)? $this->backurl :$url;
		toolsLIB::Msg($msg, $url, $second);
	}

	/**
	 * 获取get参数
	 */
	function get($var = '', $filter = array())
	{
		return $this->request->getVar($var, $filter);
	}

	/**
	 * 获取post参数
	 */
	function post($var = '', $filter = array())
	{
		return $this->request->postVar($var, $filter);;
	}

	/**
	 * Loader类
	 */
	function loadModel($classPath)
	{
		return sys_loader::loadModel($classPath);
	}
}
?>