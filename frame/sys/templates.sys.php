<?php
class sys_templates
{
	private $path;		//模板路径
	static $_instance;	//模板实例
	private $compPath;	//编译路径
	private $compName;	//编译文件名
	private $tplName;	//模板文件名
	private $tplTime;	//模板文件修改时间
	private $args;		//写入模板的变量
	private $isCompile; //是否每次都编译
	private $isZip;		//是否压缩html代码


	public function __construct($path = '', $compPath = '')
	{
		$this->setTmplatesPATH($path);
		$this->setCompilePATH($compPath);
		$this->isCompile = true;
		$this->isZip = false;
	}

	//获取模板实例
	public static function getInstance($path = '', $compPath = '')
	{
		if(!(self::$_instance instanceof self))
		{
			self::$_instance = new self($path, $compPath);
		}
		return self::$_instance;
	}

	//设置模板路径
	public function setTmplatesPATH($path)
	{
		$path = rtrim($path, '/').DIRECTORY_SEPARATOR;
		if(!is_dir($path))
		{
			$this->path = $path;
		}
		else
		{
			$this->path = $path;
		}
	}

	//设置模板编译路径
	public function setCompilePATH($path)
	{
		$path = rtrim($path, '/').DIRECTORY_SEPARATOR;
		if(!is_dir($path))
		{
			$this->compPath = $path;
		}
		else
		{
			$this->compPath = $path;
		}
	}

	//设置模板是否每次都编译
	function setIsCompile($bool)
	{
		$this->isCompile = $bool;
	}

	//设置模板是否进行压缩
	function setIsZip($bool)
	{
		$this->isZip = $bool;
	}

	//显示模板
	public function display($tmpl, $args = array())
	{
		$this->args = $args;
		$this->tplName = $this->path . $tmpl;			//模板文件名
		$this->compName = $this->compPath.md5($this->tplName).'.php';	//编译后文件名
		$this->tplTime = filemtime($this->tplName);	//模板最后修改时间
		if(!file_exists($this->tplName))
		{
			echo '模板'.$this->tplName.'不存在！';
		}
		else
		{
			if($this->isZip && Extension_Loaded('zlib'))Ob_Start('ob_gzhandler');
			if($this->isCompile || !file_exists($this->compName))
			{
				$this->compile();
			}
			else
			{
				//如果开启文件不改变则不编译文件 则修改在这里传递变量
				if(!$this->isCompile && is_array($this->args) && !empty($this->args))extract($this->args);
				include($this->compName);
			}
			if($this->isZip && Extension_Loaded('zlib')) Ob_End_Flush();
		}
		exit;
	}

	//编译模板 \\$\${1}
	function compile()
	{
		if(is_array($this->args) && !empty($this->args))extract($this->args);
		$tpl = file_get_contents($this->tplName);

		//替换include函数
		$tpl = $this->incLoop($tpl);
		//替换变量
		$tpl = preg_replace_callback("/<!--\{[\s]{0,}\\$([A-Za-z0-9_]{1,})([\.A-Za-z0-9_]{0,})[\s]{0,}\}-->/i",array($this, "arrayCallBack") , $tpl);

		//替换语句中的变量
		$tpl = preg_replace_callback("/<!--\{([^\\$\/]*)(\\$[A-Za-z0-9_]{1,}[\.A-Za-z0-9_]{0,}[^\}]{0,})\}-->/i",array($this, "arrayOtherCallBack") , $tpl);

		//替换常量
		$tpl = preg_replace("/<!--\{\s*C:([A-Za-z0-9_]{1,})\s*\}-->/i", "<?php echo defined('\${1}')?constant('\${1}'):'';?>", $tpl);

		//替换循环语句
		$tpl = preg_replace("/<!--\{\s*foreach\s+\\$(.*?)\s+as\s+\\$(.*?)\s*=>\s*\\$(.*?)\}-->\s*/i", "<?php if(is_array(\$\${1})){ foreach(\$\${1} as \\$\${2}=>\\$\${3}){?>", $tpl);
		$tpl = preg_replace("/<!--\{\s*\/foreach\s*\}-->/i", "<?php }}?>", $tpl);

		//替换if语句
		$tpl = preg_replace("/<!--\{\s*if\s+(.*?)\s*\}-->\s*/i", "<?php if(\\1){ ?>", $tpl);
		$tpl = preg_replace("/<!--\{\s*else\s*\}-->\s*/i", "<?php }else{ ?>", $tpl);
		$tpl = preg_replace("/<!--\{\s*else\s*if(.*?)\s*\}-->\s*/i", "<?php }else if(\\1){ ?>", $tpl);
		$tpl = preg_replace("/<!--\{\s*\/if\s*\}-->\s*/i", "<?php } ?>", $tpl);


		//替换for语句
		$tpl = preg_replace("/<!--\{\s*for\s+(.*?)\s*\}-->\s*/i", "<?php for(\\1){ ?>", $tpl);
		$tpl = preg_replace("/<!--\{\s*\/for\s*\}-->\s*/i", "<?php } ?>", $tpl);

		//替换需要显示返回值的函数
		$tpl = preg_replace("/<!--\{\s*P:\s*([A-Za-z0-9_]{1,}\(.*?\))\s*[;]?\s*\}-->\s*/i", "<?php echo \\1 ; ?>", $tpl);

		//替换其他函数
		$tpl = preg_replace("/<!--\{\s*([A-Za-z0-9_]{1,}\(.*?\))\s*[;]?\s*\}-->\s*/i", "<?php \\1 ; ?>", $tpl);

		$tpl = '<?php if($this->tplTime != "'.$this->tplTime.'"){$this->compile($this->args);}?>'.$tpl;
		//if($this->isZip)$tpl = $this->compress_html($tpl);
		file_put_contents($this->compName, $tpl);
		include $this->compName;
		exit;
	}

	//处理替换include函数的callback
	function incCallBack($matches)
	{
		$content = array();
		$path = $this->path.trim($matches[1]);
		if(file_exists($path))
		{
			$content = file_get_contents($path);
			$content = $this->incLoop($content);
			return $content;
		}
	}

	//递归处理每个页面的include
	function incLoop($tpl)
	{
		if(preg_match("/<!--\{\s*include\s*path\s*\=\s*(.*?)\s*\}-->\s*/i", $tpl, $tmp)> 0)
		{
			//替换include函数
			$tpl = preg_replace_callback("/<!--\{\s*include\s*path\s*\=\s*(.*?)\s*\}-->\s*/i", array($this, "incCallBack") , $tpl);
			return $tpl;
		}
		return $tpl;
	}

	//处理获取数组中变量的callback
	function arrayCallBack($matches)
	{
		$arr = empty($matches[2])?'':"['".substr($matches[2], 1)."']";
		return "<?php if(isset(\$".$matches[1].$arr."))echo \$".$matches[1].$arr.";?>";
	}
	//处理获取数组中变量的callback
	function arrayOtherCallBack($matches)
	{
		$matches[2] = preg_replace("/([a-zA-Z0-9_]*)\.([a-zA-Z0-9_]*)/", "$1['$2']", $matches[2]);
		$arr = empty($matches[3])?'':"['".substr($matches[3], 1)."']";
		return '<!--{'.$matches[1].$matches[2].'}-->';
	}

	function compress_html($string)
	{
		$string = str_replace("\r\n", '', $string);
		$string = str_replace("\n", '', $string);
		$string = str_replace("\t", '', $string);
		$pattern = array (
			"/> *([^ ]*) *</",
			"/[\s]+/",
			"//",
			"/\" /",
			"/ \"/",
			"'/\*[^*]*\*/'"
		);
		$replace = array (
			">\\1 <",
			" ",
			"",
			"\"",
			"\"",
			""
			);
		return preg_replace($pattern, $replace, $string);
	}
}
