快速开始
-------
1. 下载 框架代码 解压到 网站跟目录目录机构如下

	```
	|-- /
	|-- app
	   |-- controller
	   |-- extends
	   |-- files
	   |-- plugin
	   |-- config.php
	|-- frame
	|-- .htaccess
	|-- README.md
	|-- index.php
	```
	`app` 目录为应用程序目录，`frame` 目录为框架目录，`index.php` 为入口文件
	> `app` 目录是为您简单配置好的一个APP目录，您也可以建立自己的app程序，具体请参考，高级使用。

2. 在app/controller建立一个名字为testController.php的文件(所有的controller文件必须以Controller为后缀),文件内容如下：

	```
	<?php
	class testController extends My_Controller
	{
		public function __construct()
		{
			parent::__construct();
		}

		public function indexAction()
		{
			$data['show'] = "This Is a lessPHP Program!";
			$this->display('test.html', $data);
		}
	}
	?>
	```
	**注意 classname 跟文件名必须相同**

3. 在app/templates/default(模版目录可以在config中配置) 建立一个名字为 test.html 的文件,文件内容如下：
	```
	<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<title>第一个lessPHP程序</title>
		</head>
		<body>
			<p><!--{$show}--></p>
			<p>第一个lessPHP程序</p>
		</body>
	</html>
	```
4. 将app/files/templates_c 的权限设置为 可写 777
5. 运行 `您的程序 http://域名/test` ，你将看到刚才设置的页面。

进阶使用
-------
> 在本节教程中，将会带您 使用 MySQL 创建一个留言板程序

1. 首先建立数据库，和表结构
	```
	CREATE TABLE `message` (

  	`id` int(11) NOT NULL auto_increment,

	  `nickname` varchar(50) collate utf8_unicode_ci NOT NULL COMMENT '昵称',

	  `content` varchar(200) collate utf8_unicode_ci NOT NULL COMMENT '内容',

	  `parentid` int(11) NOT NULL default '0' COMMENT '回复id',

	  `createtime` int(11) NOT NULL COMMENT '创建时间',

	  `ip` varchar(15) collate utf8_unicode_ci NOT NULL COMMENT '发表IP地址',

	  `status` tinyint(4) NOT NULL default '1' COMMENT '状态 1正常 0 审核 -1删除',

	  PRIMARY KEY  (`id`)

	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='留言板' AUTO_INCREMENT=1 ;
	```
2. 打开 `app` 目录下的 `config.php` 文件(如果不存在，请复制 `frame` 框架目录下的`config_default.php` 到 app目录下，并更名为 `config.php` )，修改 `$config['database']['master']` 数组，配置您的数据库信息。

3. 进入 `app/model` 建立 messageModel.php 文件,将以下代码写入

	```
	<?PHP
	class messageModel extends sys_model
	{

		private $_table = 'message';

		public function __construct()
		{   
		    parent::__construct();
		}   

		/** 
		 * 分页获取留言列表
		 *
		 * @param mixed $page 页数
		 * @param mixed $count 数量
		 * @access public
		 * @return array
		 */
		public function getList($page, $count = 10) 
		{   
		    $page = $page > 0 ? $page - 1 : 0;
		    $sql = "SELECT * FROM ".$this->_table . " LIMIT {$page}, {$count}";
		    return $this->db()->getAll($sql);
		}   

		/** 
		 * 增加留言
		 * 
		 * @param mixed $nickname 昵称
		 * @param mixed $content 内容
		 * @access public
		 * @return int
		 */
		public function add($nickname, $content)
		{   
		    $args = array();
		    $args['nickname'] = $nickname;
		    $args['content'] = $content;
		    $args['createtime'] = time();
		    $args['ip'] = httpLIB::getIp();
		    $args['status'] = 1;
		    return $this->db()->add($args, $this->_table);
		}   
	}
	```
> messageModel 类名字必须与model的文件名称相同，文件继承自sys_model

4. 进入 `app/Controller` 新建indexController.php文件,内容如下

	```
	<div>
		<ul>
		<!--{foreach item = i from = $list}-->
		    <li>
		        <div>
		            <strong style = 'font-weight:bold'>[<!--{$i.nickname}-->]</strong>: 
		            <p style = 'margin-left:25px;'><!--{$i.content}--></p>
		        </div>
		    </li>
		<!--{/foreach}-->
		</ul>
	</div>
	<div>
		<form action = '/index/add' method = 'post'>
		<table>
		    <tr>
		        <td>昵称:</td><td><input type="text" name = "nickname" /></td>
		    </tr>
		    <tr>
		        <td>内容:</td><td><textarea name = 'content'></textarea></td>
		    </tr>
		    <tr>
		        <td colspan = 2><input type = 'submit' value = '提交'></td>
		    </tr>
		</table>
		</form>
	</div>
	```
> 采用 `Smarty` 模板引擎，也可以使用内置模板引擎或者其他的模板引擎

5. 运行程序

内置模版引擎语法
-------

`<!--{ 语法区域 }-->`

1. 获取常量

	```
	<!--{C:常量名}-->
	```

2. 获取变量

	```
	<!--{$变量名}-->
	```

3. 循环

	```
	<!--{foreach as $k => $v}-->
		循环体
	<!--{/foreach}-->
	
	<!--{for $i=0; $i < 10;$++}-->
		循环体
	<!--{/for}-->
	```

4. 调用函数

	```
	<!--{P:函数名}-->
	```

5. if语句

	```
	<!--{if 表达式}-->
	<!--{/if}-->
	```


