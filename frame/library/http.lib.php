<?php
/**
 * http 操作类
 */
class httpLIB
{
	/**
	 * 获取url返回值，curl方法
	 */
	public static function curlGet($url, $timeout = 1, $header = array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}


	/**
	 * 提交post请求，curl方法
	 *
	 * @param string $url         请求url地址
	 * @param array  $data        变量数组
	 * @return string             请求结果
	 */
	public static function curlPost($url, $header, $data, $port="", $timeout = 10)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_PORT, $port);
		curl_setopt($ch,  CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$user_agent = 'CURL Request';
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); //HTTP请求User-Agent:头
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}

	/**
	 * 提交post请求，socket方法
	 *
	 * @param string $url         请求url地址
	 * @param array  $data        变量数组
	 * @return string             请求结果
	 */

	public static function socketPost($xml)
	{
		$fp = fsockopen("$url", $port, $errno, $errstr, 10) or exit($errstr."--->".$errno);
		//构造post请求的头
		$header = "POST /uploadxml HTTP/1.1\r\n";
		$header .= "Host:10.207.50.9\r\n";
		$header .= "Accept: */*\r\n";
		$header .= "Content-Length: ".strlen($xml)."\r\n";
		$header .= "Content-Type: text/xml; charset=UTF-8\r\n\r\n";
		//添加post的字符串
		$header .= $xml;
		//发送post的数据
		fputs($fp,$header);
		$inheader = 1;
		while (!feof($fp)) {
			$line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据
			if ($inheader && ($line == "n" || $line == "")) {
				$inheader = 0;
			}
			if ($inheader == 0) {
			}
		}
		fclose($fp);
	}

	/**
	 * 获取客户端IP
	 *
	 * @return string $ip
	 */
	public static function getIp()
	{
		if (getenv('HTTP_CLIENT_IP'))
		{
			$ip = getenv('HTTP_CLIENT_IP');
		}
		elseif (getenv('HTTP_X_FORWARDED_FOR'))
		{
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('REMOTE_ADDR'))
		{
			$ip = getenv('REMOTE_ADDR');
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}
