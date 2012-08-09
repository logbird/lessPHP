<?php
/**
 * Router处理
 */
class sys_router
{
	private $_action = 'index';
	private $_controller = 'index';
	private $_controllerClass;
	private $_param = array();
	private $controllerPath = '';	//自定义controller目录
	private $_appPath = '';
	private $_module;
	private $_level;
	private $_urlPath;

	function __construct($level = 1)
	{
		//设置默认的控制器目录
		$this->_appPath = sys_config::Get('controller_dir') . '/';
		//获取path
		$this->setPath();
		//处理自定义url
		$this->customUrl();
		//拆分url
		$this->parseUrl($level);
		$this->runController();
	}

	/**
	 * 分析url
	 * @params $level 目录层级 默认为 1级
	 */
	private function parseUrl($level = 1)
	{
		$param = explode('/', $this->_urlPath);
		//判断是否到目录底部
		if($level > count($param))
		{
			return false;
		}
		$c = implode('/', array_slice($param, 0, $level));
		$a = isset($param[$level])&&!empty($param[$level])?$param[$level]:'';
		$c = $this->isLetter($c)?$c:sys_config::Get('controller_def');
		$a = $this->isLetter($a)?$a:sys_config::Get('action_def');

		//定义控制器名称
		$this->_controller =  $c;
		//定义控制器类名
		$this->_controllerClass = current(array_slice(explode('/', $c), -1));
		//定义Action
		$this->_action = $a;
		//定义层级
		$this->_level = $level;
		return true;
	}

	private function runController()
	{
		//定义控制器
		$c = $this->_controller;
		$a = $this->_action;
		$controllerClass = $this->_controllerClass . 'Controller';
		$action = $a . 'Action';

		//定义控制器文件
		$controllerFile = $this->_appPath .$c . 'Controller.php';
		if(file_exists($controllerFile))
		{
			require_once($controllerFile);
			if(!class_exists($controllerClass, false))
			{
				$this->notFound("Class {$controllerClass} is not found!");
			}
			$obj = new $controllerClass();
			if(!method_exists($obj, $action))
			{
				$this->notFound("Action {$a} is not found!");
			}
			$obj->$action();
			exit;
		}
		else if($this->parseUrl($this->_level+1))
		{
			$this->runController();
		}else
		{
			$this->notFound("Controller {$controllerFile} is not found!");
		}
	}

	private function notFound($msg = '')
	{
        try
        {
            throw new sys_exception($msg);
        }catch(sys_exception $ce)
        {
            $ce->showMsg();
        }
		exit;
	}

	/**
	 * 获取url的path部分
	 *
	 * @return string
	 * modify for http://punny.skiyo.cn/
	 */
	function setPath()
	{
        $path = '';
        if(isset($_SERVER['REQUEST_URI']))
		{
            $path = $_SERVER['REQUEST_URI'];
        }else
		{
            if(isset($_SERVER['argv']))
			{
                $path = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
            }else
			{
                $path = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
            }
        }

        //兼容iis6的gbkurl
        if(isset($_SERVER['SERVER_SOFTWARE']) && false !== stristr($_SERVER['SERVER_SOFTWARE'], 'IIS'))
		{
        	if(function_exists('mb_convert_encoding'))
			{
        		$path = mb_convert_encoding($path, 'UTF-8', 'GBK');
        	}else
			{
        		$path = @iconv('GBK', 'UTF-8', @iconv('UTF-8', 'GBK', $path)) == $path ? $path : @iconv('GBK', 'UTF-8', $path);
        	}
        }
        //for ie6 header location
        $r = explode('#', $path, 2);
        $path = $r[0];
        //for iis6
        $path = str_ireplace('index.php', '', $path);
        //for subdirectory
        $t = parse_url(URL);
        $path = str_replace($t['path'], '/', $path);
		$path = ltrim($path, '/');
		$path = parse_url($path);
		$path = $path['path'];
		$this->_urlPath = $path;
    }

	/**
	 * 自定义url处理
	 */
	function customUrl()
	{
		$path = $this->_urlPath;
		//自定义url处理
		$routes = sys_config::Get('routes');
		foreach($routes as $k => $v)
		{
			if($path == trim($k, '/'))
			{
				$path = $v;
			}else
			{
				//替换正则
				$path = preg_replace('/^'.$k.'$/', $v, $path);
			}
		}
		$path = parse_url($path);
		//处理http参数
		if(isset($path['query']))
		{
			$path['query'] = explode("&", $path['query']);
			foreach($path['query'] as $v)
			{
				$v = explode('=', $v);
				$k = $v[0];
				$v = $v[1];
				$_GET[$k] = $_REQUEST[$k] = $v;
			}
		}
		$path = trim($path['path'], '/');
		$this->_urlPath = $path;
	}

	/**
	 * 判断第一个字符是否为字母
	 *
	 * @param string $char
	 * @return boolean
	 */
	private function isLetter($char) {
		if(empty($char))return false;
		$ascii = ord($char{0});
		return ($ascii >= 65 && $ascii <= 90) || ($ascii >= 97 && $ascii <= 122);
	}
}
?>
