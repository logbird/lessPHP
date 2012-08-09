<?php
!defined('LESS_ROOT') && exit('access deined!');
header("Content-type:text/html;charset=utf-8");
header("Cache-Control:no-cache,must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

@date_default_timezone_set("PRC");

//定义 自动加载
spl_autoload_register('__autoload');
//**********************************加载初始化文件************************************
//加载程序配置文件
if(file_exists(CONFIG))
{
	require_once(CONFIG);
}else
{
	echo "配置文件不存在，复制框架目录的config_default.php，并重命名为config.php到您的app目录下.";
	exit;
}

//**********************************网站运行模式******************************************
$online = true;
if(sys_config::Get('runMode') == 'dev')
{
	$online = false;
}
define('OnLine', $online);

if(OnLine)
{
	ini_set('display_errors', 'Off');
}else
{
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
}
unset($online);

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
define('ROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));

//站点URL
define('URL', "http://".$_SERVER['SERVER_NAME'].ROOT);

//用户 资源URL 路径
define('REC', URL . trim($_APP_PATH, '/') . '/' . trim(sys_config::Get('resource'), '/') . '/');

//**********************************自动加载类****************************************
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
	}else
	{
		$class = sys_loader::getPlugin($class);
	}
	require_once $class;
}
