<?php
/**
 * 控制器抽象类
 * 
 * @abstract
 * @package 
 * @version $id$
 * @copyright 1997-2005 The PHP Group
 * @author Tobias Schlitt <toby@php.net> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
abstract class sys_controller
{
	protected $backurl = '';

	function __construct($tmpl = '')
	{
		//开启input过滤
		$this->input = new sys_request();
		$this->backurl = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:'/';
	}

	/**
	 * 获取get参数
	 */
	function get($var = '', $filter = array())
	{
		return $this->input->getVar($var, $filter);
	}

	/**
	 * 获取post参数
	 */
	function post($var = '', $filter = array())
	{
		return $this->input->postVar($var, $filter);;
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
