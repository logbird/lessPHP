<?php
/**
 * 程序配置文件
 */
!defined('LESS_ROOT') && exit('access deined!');
defined('CONFIG_INCLUDE') && exit('Configruge Duplicates!');
define('CONFIG_INCLUDE', true);


$config = array();

//配置框架路径
$config['controller_dir'] = APP . 'controller';
$config['model_dir'] = APP . 'model';
$config['plugin_dir'] = APP . 'plugin';
$config['extends_dir'] = APP . 'extends';

//模版资源路径
$config['templates_dir'] = APP . 'templates/default/';
$config['comple_dir'] = APP . 'files/templates_c';
$config['resource'] = '/templates/new/';

//默认controller 默认 action
$config['controller_def'] = 'index';
$config['action_def'] = 'index';

//运行模式 dev 为开发模式 上线请设置为 online
$config['runMode'] = 'dev';

/**
 * 路由配置，手动配置rewrite 自定义路径
 * key 为正则表达式 value 所执行的真是url,正则表达式不要加开始结束符
 * 例如：匹配 / 为 /index/index 正则表达式为 /^\/$/ 这里的 rewrite则写为 '\/' => '/index/index',
 * 【注意】【value必须使用单引号，否则$1替换符将失效,双引号 需要对 $进行转义】
 */
$config['routes'] = array(
);

/**
 * 插件注册
 * className => array(path, lazyLoad) 
 * lazyLoad: true 延迟加载 false 立即加载插件 如果文件为函数 请使用立即加载 默认延迟加载
 */ 
$config['plugin'] = array(
    'Smarty' => array('Smarty/Smarty.class.php', false),
);

//加密密匙
$config['encrypt'] = 'zjhsrsdfcbtrwepd';
//是否开启 错误处理机制
$config['errHandle'] = false;

//mysql设置
$config['database']['master']['host'] = 'localhost';    //mysql 主机
$config['database']['master']['port'] = '3306';			//mysql 端口
$config['database']['master']['dbname'] = 'slmei';	    //mysql 数据库名
$config['database']['master']['uname'] = 'root';		//mysql 用户名
$config['database']['master']['upwd'] = '123';			//mysql 密码
$config['database']['charset'] = 'utf8';        		//mysql 编码
$config['database']['debug'] = false;			        //mysql 是否开始调试模式
$config['database']['debugFile'] = '';  			    //mysql 该字段不为空字符串的话 则将调试信息输出到文件中
$config['database']['errReport'] = true;			    //mysql 是否显示sql语句错误

//缓存设置
$config['cache']['options'] = 'file';					//mem 为 使用memcache作为缓存 file 为文件缓存



?>
