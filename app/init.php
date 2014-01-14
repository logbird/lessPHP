<?php
/**
 * 程序初始化文件
 */
header("Content-type:text/html;charset=utf-8");
header("Cache-Control:no-cache,must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
@date_default_timezone_set("PRC");

!defined('LESS_ROOT') && exit('access deined!');
defined('INIT_INCLUDE') && exit('Configruge Duplicates!');
define('INIT_INCLUDE', true);
define('CONFIG_DIR', APP.'config/');



;

?>
