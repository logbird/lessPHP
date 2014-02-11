<?php
!defined('LESS_ROOT') && exit('access deined!');

//定义 自动加载 防止与 smarty等类库冲突
spl_autoload_register('__autoload');
//**********************************加载初始化文件************************************
//加载程序配置文件
if(file_exists(INIT))
{
    require_once(INIT);
} else
{
	echo "初始化文件不存在，复制框架目录的init_default.php，并重命名为init.php到您的app目录下.";
	exit;
}

//**********************************网站运行模式******************************************
if(sys_config::Get('runMode') == 'dev')
{
    define('OnLine', false);
}else
{
    define('OnLine', true);
}

if(OnLine)
{
	ini_set('display_errors', 'Off');
}else
{
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
}

if(sys_config::Get('errHandle') && !OnLine)
{
    set_error_handler('exceptionHanddle', E_ALL ^ E_NOTICE);
}

//错误机制
function exceptionHanddle($errno, $errstr)
{
    $ce = new sys_exception($errstr, $errno); 
    $ce->showMsg();
}

//**********************************定义路径******************************************
//虚拟根目录
define('VROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));

//站点URL
if(isset($_SERVER['SERVER_NAME']) && $argc == 0)
{
	define('URL', "http://".$_SERVER['SERVER_NAME'].VROOT);
}else
{
	define('URL', LESS_ROOT. VROOT);
}

//用户 资源URL 路径
define('REC', URL . trim($_APP_PATH, '/') . '/' . trim(sys_config::Get('resource'), '/') . '/');

//**********************************插件加载******************************************
$plugins = sys_config::Get('plugin');
foreach($plugins as $k => $v)
{
    //默认 false 为立即加载
    $lazyLoad = isset($v[1]) ? $v[1] : false;
    sys_loader::register($k, $v[0], $lazyLoad);
}
unset($plugins);

//**********************************自动加载******************************************
function __autoload($class)
{
	if(substr($class, 0, 4) == 'sys_')
	{
		$class = SYSTEM . 'sys' . DIRECTORY_SEPARATOR . substr($class, 4) . '.sys.php';
	}else if(substr($class, 0, 3) == 'My_')
	{
		$class = sys_config::Get('extends_dir') . DIRECTORY_SEPARATOR . $class . '.php';
	}else if(substr($class, -3) == 'LIB')
	{
		$class = SYSTEM . 'library' . DIRECTORY_SEPARATOR . substr($class, 0, strpos($class, 'LIB')).'.lib.php';
	}elseif(file_exists($class.'.php'))
	{
		$class .= '.php';
	}elseif(file_exists(sys_config::Get('module_dir') .DIRECTORY_SEPARATOR. $class.'.php'))
	{
		$class = sys_config::Get('module_dir') . DIRECTORY_SEPARATOR . $class . '.php';
	} else {
		$class = sys_loader::getPlugin($class);
	}
    if($class && file_exists($class)) {
	    require_once $class;
    }
}
