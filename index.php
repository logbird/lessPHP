<?php

//LESS_ROOT 根目录
define ('LESS_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
//运行时间
define('_RUNTIME', microtime(true));
//定义运行模式
!defined('IS_CONSOLE') && define('IS_CONSOLE', false);


/*
 |----------------------------------用户配置区-------------------------------------------
*/
/**
 * 定义app的目录
 */
$_APP_PATH = 'app';

/**
 * 定义框架的目录
 */
$_SYSTEM_PATH = 'frame';
/*
 |---------------------------------------------------------------------------------------
*/



/*
 |----------------------------------系统启动区（无需修改）-------------------------------
*/
//定义applaction路径
define('APP', LESS_ROOT . ltrim($_APP_PATH, '/') . DIRECTORY_SEPARATOR);

//定义框架路径
define('SYSTEM', LESS_ROOT . ltrim($_SYSTEM_PATH, '/') . DIRECTORY_SEPARATOR);

//定义配置文件路径
define("INIT", APP . 'init.php');

//加载框架初始化文件
require(SYSTEM . 'lessPHP.php');

//加载控制器
new sys_router();
/*
 |---------------------------------------------------------------------------------------
*/
