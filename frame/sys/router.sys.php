<?php
/**
 * 核心路由类 包含 Cli路由 和 Http路由两种
 * 
 * @package 
 * @version $id$
 * @copyright @copyright 2005-2012 360.CN All Rights Reserved.
 * @author logbrid <logbird@126.com> 
 * @license 
 */
class sys_router
{
	private $_action = 'index';
	private $_controller = 'index';
	private $_controllerClass;
	private $_param = array();
	private $controllerPath = '';	//自定义controller目录
	private $_appPath = '';
	private $_level;
	private $_urlPath;

    /**
     * 构造函数
     *
     * @param int $level
     * @access protected
     * @return void
     */
	function __construct($level = 1)
	{
        //如果是cli脚本则解析 命令行参数后转为path
        if (IS_CONSOLE) {
            //设置默认的控制器目录
		    $this->_appPath = sys_config::Get('commands_dir') . '/';
            $this->cliRun();
        } else {
            //设置默认的控制器目录
		    $this->_appPath = sys_config::Get('controller_dir') . '/';
            $this->httpRun($level);
        }
	}

    /**
     * 使用HTTP方式 运行路由
     *
     * @param int $level
     * @access public
     * @return void
     */
    public function httpRun($level = 1)
    {
		//获取path
		$this->setPath();
		//处理自定义url
		$this->customUrl();
		//拆分url
		$this->parseUrl($level);
		$this->runHttpController();
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
            $path = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
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
        //删除锚点
        $r = explode('#', $path, 2);
        $path = $r[0];
        //for iis6
        $path = str_ireplace('index.php', '', $path);
		$path = ltrim($path, '/');
		$path = parse_url($path);
		$path = isset($path['path']) ? $path['path'] : '';
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
                //改为 parsestr
				$_GET[$k] = $_REQUEST[$k] = $v;
			}
		}
		$path = trim($path['path'], '/');
		$this->_urlPath = $path;
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

    /**
     * 执行相应controller
     *
     * @access private
     * @return void
     */
	private function runHttpController()
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

    /**
     * 使用Cli方式运行路由
     *
     * @access public
     * @return void
     */
    public function cliRun()
    {
        //设置Cli传递的参数和路由信息
        $this->parseCliParams();
        //执行Cli的程序
        $this->runCliController();
    }

    /**
     * 解析Cli的命令参数
     *
     * @access public
     * @return void
     */
    public function parseCliParams()
    {
        $path = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '/';
        //获取系统参数
        $args = $this->parseArgs();
        $c = isset($args[0]) ? $args[0] : '';
        $a = isset($args[1]) ? $args[1] : '';

		//定义控制器名称
		$this->_controller =  $c;
		//定义控制器类名
		$this->_controllerClass = current(array_slice(explode('/', $c), -1));
		//定义Action
		$this->_action = $a;
        //定义命令行参数
        $this->_param = $args;
    }

    /**
     * 执行相应controller(Cli)
     *
     * @access private
     * @return void
     */
	private function runCliController()
	{
		//定义控制器
		$c = $this->_controller;
		$a = $this->_action;
		$controllerClass = $this->_controllerClass . 'Controller';
		$action = $a . 'Action';

        if (empty($c)) {
			$this->notFound("Script invalid!");
        }
        if (empty($a)) {
			$this->notFound("Action invalid!");
        }

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
            //运用反射机制 设置 执行脚本的参数
            $method = new ReflectionMethod($obj, $action);
            $parameters = $method->getParameters();
            $args = array();
            foreach ($parameters as $k => $v)
            {
                $argsName = $v->getName();
                $argsValue = null;
                if (isset($this->_param[$argsName]))
                {
                    $argsValue = $this->_param[$argsName];
                } else if ($v->isOptional()){
                    //设置默认值
                    $argsValue = $v->getDefaultValue();
                }
                $args[$argsName] = $argsValue;
            }
            $method->invokeArgs($obj, $args);
			exit;
		} else
		{
			$this->notFound("Controller {$controllerFile} is not found!");
		}
	}

    /**
     * 命令行参数解析
     *
     * @param mixed $argv
     * @access public
     * @return void
     *
     * @author Patrick Fisher <patrick@pwfisher.com>
     * @see https://github.com/pwfisher/CommandLine.php
     */
    public function parseArgs($argv = null) {
        $argv = $argv ? $argv : $_SERVER['argv'];
        array_shift($argv);
        $o = array();
        for ($i = 0, $j = count($argv); $i < $j; $i++) {
            $a = $argv[$i];
            if (substr($a, 0, 2) == '--') { 
                $eq = strpos($a, '=');
                if ($eq !== false) { 
                    $o[substr($a, 2, $eq - 2)] = substr($a, $eq + 1); }
                else { 
                    $k = substr($a, 2);
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') { 
                        $o[$k] = $argv[$i + 1]; $i++; 
                    }
                    else if (!isset($o[$k])) { $o[$k] = true; } 
                }
            }
            else if (substr($a, 0, 1) == '-') {
                if (substr($a, 2, 1) == '=') { 
                    $o[substr($a, 1, 1)] = substr($a, 3); 
                }
                else {
                    foreach (str_split(substr($a, 1)) as $k) { 
                        if (!isset($o[$k])) {
                            $o[$k] = true; 
                        } 
                    }
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $o[$k] = $argv[$i + 1]; $i++; } 
                }
            }
            else { 
                $o[] = $a; 
            }
        }
        return $o;
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

    /**
     * 路由失败的异常处理
     *
     * @param string $msg
     * @access private
     * @return void
     */
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


}
?>
