<?php
/**
 * request请求类 获取request请求传送过来的参数
 */
//session_start();
class sys_request
{
	private $requestData =  array();

	function __construct()
	{
		$this->clearStrips();
		$this->xssFilter();
	}

	/**
	 * 重新封装获取get参数的函数
	 *
	 */
	public function getVar($var = '', $filter = array())
	{
		//如果 传递了 屏蔽过滤参数 进行非过滤字段处理
		if($filter !== array())
		{
			//如果 filter 是 false 并且 var不是空 则认为是 指定参数 不过滤 否则 var为空 filter为false的时候 为全部不过滤
			if($filter === false && !empty($var))
			{
				$filter = $var;
			}
			$this->noFilter($filter, 'get');
		}
		if(empty($var))
		{
			return $this->requestData['get'];
		}else
		{
			return isset($this->requestData['get'][$var])?$this->requestData['get'][$var]:false;
		}
	}

	/**
	 * 重新封装获取post参数的函数
	 *
	 */
	public function postVar($var = '', $filter = array())
	{
		if($filter !== array())
		{
			//如果 filter 是 false 并且 var不是空 则认为是 指定参数 不过滤 否则 var为空 filter为false的时候 为全部不过滤
			if($filter === false && !empty($var))
			{
				$filter = $var;
			}
			$this->noFilter($filter, 'post');
		}
		if(empty($var))
		{
			return $this->requestData['post'];
		}else
		{
			return isset($this->requestData['post'][$var])?$this->requestData['post'][$var]:false;
		}
	}

	/**
	 * 过滤xss
	 *
	 */
	private function xssFilter()
	{
		$this->requestData = array(
			'get' => $this->filter($_GET),
			'post'=> $this->filter($_POST),
		);
		if(isset($_SERVER['QUERY_STRING']))
		{
			$_SERVER['QUERY_STRING'] = str_replace("&amp;", '&', htmlspecialchars(urldecode($_SERVER['QUERY_STRING']), ENT_QUOTES));
		}
	}

	/**
	 * get post参数过滤
	 * @param array data 要过滤的数据
	 * @param array isFilter 禁止进行处理的 参数key
	 * return array/string
	 */
	private function filter($data, $isFilter = array())
	{
		if(is_array($data))
		{
			foreach($data as $k => $v)
			{
				if(!empty($isFilter) && in_array($k, $isFilter))continue;
				if(is_array($v))
					$this->filter($v);
				else
					$data[$k] = htmlspecialchars(trim($v), ENT_QUOTES);
			}
		}else
		{
			$data = htmlspecialchars(trim($data), ENT_QUOTES);
		}
		return $data;
	}

	/**
	 * 处理get post禁止过滤的参数
	 * @param array data 要禁止过滤的参数key 如果需要全部不过滤 则传递false
	 * @param array method 要禁止过滤的 数组类型 默认 all 还可以选择 get post，all为全禁止
	 * return array/string
	 */
	private function noFilter($data = false, $method = 'all')
	{
		//全部禁止过滤
		if($data === false)
		{
			if($method == 'all' || $method == 'get')
				$this->requestData['get'] = $_GET;
			if($method == 'all' || $method == 'post')
				$this->requestData['post'] = $_POST;
			return true;
		}
		//禁止过滤data数组内数据
		if(is_array($data) && !empty($data))
		{
			foreach($data as $k => $v)
			{
				if(($method == 'all' || $method == 'get') && isset($this->requestData['get'][$v]))
					$this->requestData['get'][$v] = $_GET[$v];
				if(($method == 'all' || $method == 'post') && isset($this->requestData['post'][$v]))
					$this->requestData['post'][$v] = $_POST[$v];
			}
		}else
		{
			//禁止过滤key为data的数据
			if(($method == 'all' || $method == 'get') && isset($this->requestData['get'][$data]))
				$this->requestData['get'][$data] = $_GET[$data];
			if(($method == 'all' || $method == 'post') && isset($this->requestData['post'][$data]))
				$this->requestData['post'][$data] = $_POST[$data];
		}
	}

	private function stripslashesDeep($value)
	{
		$value = is_array($value) ? array_map(array($this, 'stripslashesDeep'), $value) : stripslashes($value);
		return $value;
	}

	/**
	 * 递归去除转义字符
	 */
	private function clearStrips()
	{
		if (get_magic_quotes_gpc())
		{
			$_GET = $this->stripslashesDeep($_GET);
			$_POST = $this->stripslashesDeep($_POST);
			$_COOKIE = $this->stripslashesDeep($_COOKIE);
			$_REQUEST = $this->stripslashesDeep($_REQUEST);
		}
	}

}
