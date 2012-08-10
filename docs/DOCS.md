快速开发
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

2. 在controller建立一个名字为testController.php的文件(所有的controller文件必须已Controller为后缀，Controller可以在配置文件里配置),文件内容如下：

```
<?php
class indexController extends My_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
	}
}
?>
```
**注意 classname 跟文件名必须相同**

3. 



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


