<?php
/**
 * 工具类
 */
class toolsLIB
{

	/**
	 * 刷新缓冲区
	 */
	public static function flushBuffers(){
		if ( connection_aborted() )exit;
		@ob_end_flush();
		@ob_flush();
		flush();
		ob_start('self::ob_callback');
	}
	private static function ob_callback($buffer)
	{
		return $buffer . str_repeat(' ', max(0, 4097 - strlen($buffer)));
	}

	/**
	 * 对数组进行编码转换
	 *
	 * @param strint $in_charset  输入编码
	 * @param string $out_charset 输出编码
	 * @param array $arr          输入数组
	 * @return array              返回数组
	 */
	public static function iconvArray($in_charset, $out_charset, $arr)
	{
		if (strtolower($in_charset) == "utf8")
		{
			$in_charset = "UTF-8";
		}
		if (strtolower($out_charset) == "utf-8" || strtolower($out_charset) == 'utf8')
		{
			$out_charset = "UTF-8";
		}
		if (is_array($arr))
		{
			foreach ($arr as $key => $value)
			{
				$arr[$key] = iconvArray($in_charset, $out_charset, $value);
			}
		}
		else
		{
			if (!is_numeric($arr))
			{
				$arr = iconv($in_charset, $out_charset, $arr);
			}
		}
		return $arr;
	}

	/**
	 * 获取随机字符串
	 *
	 * @param integer $length 字符串长度
	 * @param integer $numeric 是否 使用随机数字
	 * @return string
	 */
	public static function randStr($length, $numeric = false)
	{
		if ($numeric)
		{
			$hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
		}
		else
		{
			$hash = '';
			$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
			$max = strlen($chars) - 1;
			for($i = 0; $i < $length; $i++)
			{
				$hash .= $chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}

	/**
	 * 将对象转为数组
	 * author liuxp
	 * @param object $object
	 * @return array
	 */
	public static function object2Array(&$object)
	{
		$object = (array)$object;
		foreach ($object as $key => $value)
		{
			if (is_object($value) || is_array($value))
			{
				if($value)
				{
					$object[$key] = object_to_array($value);
				}
				else
				{
					$object[$key] = '';
				}
			}
			else
			{
				$object[$key] = $value;
			}
		}
		return $object;
	}

	/**
	 * 读取目录中指定扩展名的文件列表，以数组形式返回
	 *
	 * @param string $dir           目录名
	 * @param array  $extensions    扩展名（为空则返回全部）
	 * @return array
	 * @example
	 * 		返回 /tmp 目录下扩展名为 txt和php 的文件列表
	 * 		getFileList('/tmp',array('txt', 'php'));
	 */
	public static function getFileList($dir, $extensions = array())
	{
		//打开目录
		$handle = opendir($dir);
		static $file_array = array();
		//读目录
		while (false != ($file = readdir($handle)))
		{
			//列出所有文件并去掉'.'和'..'
			if ($file != '.' && $file != '..')
			{
				//所得到的文件名是否是一个目录
				if ( is_dir("$dir/$file") )
				{
					//列出目录下的文件
					self::getFileList("$dir/$file", $extensions);
				}
				else
				{
					if (!empty($extensions)) {
						$path_parts = pathinfo("$dir/$file");
						if (!isset($path_parts['extension']) || !in_array($path_parts['extension'], $extensions)) {
							continue;
						}
					}
					//将读到的内容赋值给一个数组
					$file_array[] = "$dir/$file";
				}
			}
		}
		return $file_array;
	}

	/**
	 * 创建目录，如果目录存在，直接返回true
	 *
	 * @param string $dir
	 * @param 创建目录的权限 $mode
	 * @return boolean
	 */
	public static function createDir($dir=null, $mode=0777)
	{
		if ($dir == null)
		{
			return false;
		}

		if (is_dir($dir))
		{
			return true;
		}

		if (!is_dir(dirname($dir)))
		{//如果上一级不是目录，先创建
			self::createDir(dirname($dir), $mode);
		}

		if (mkdir($dir, $mode))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 获取指定月份的天数
	 *
	 * @param string $month 月份
	 * @param string $year 年份
	 */
	public static function getMonthDayNum($month, $year) {
		switch(intval($month)){
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 10:
			case 12:
				return 31;break;
			case 2:
				if ($year % 4 == 0) {
					return 29;
				} else {
					return 28;
				}
				break;
			default:
				return 30;
				break;
		}
	}

	/**
	 * 删除文件或目录
	 */
	public static function deleteFile($file){
		if (empty($file))
			return false;
		if (@is_file($file))
			return @unlink($file);
		$ret = true;
		if ($handle = @opendir($file)) {
			while ($filename = @readdir($handle)){
				if ($filename == '.' || $filename == '..')
					continue;
				if (!self::deleteFile($file . '/' . $filename))
					$ret = false;
			}
		} else {
			$ret = false;
		}
		@closedir($handle);
		if ( file_exists($file) && !rmdir($file) ){
			$ret = false;
		}
		return $ret;
	}

	/**
	 * 页面跳转
	 */
	public static function Direct($directUrl) {
		header("Location: $directUrl");
		exit;
	}

	/**
     * 显示系统信息
     *
     * @param string $msg 信息
     * @param string $url 返回地址
     * @param boolean $second 自动跳转时间 如果是0则为不自动跳转
     */
	function Msg($msg, $url='javascript:history.back(-1);', $second = 0)
	{
		if ($msg == '404') {
			header("HTTP/1.1 404 Not Found");
			$msg = '抱歉，你所请求的页面不存在！';
		}
		echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
EOT;
	if($second > 0){
		echo "<meta http-equiv=\"refresh\" content=\"".$second.";url=$url\" />";
	}
	echo <<<EOT
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>emlog system message</title>
<style type="text/css">
<!--
body {
	background-color:#F7F7F7;
	font-family: Arial;
	font-size: 12px;
	line-height:150%;
}
.main {
	background-color:#FFFFFF;
	font-size: 12px;
	color: #666666;
	width:750px;
	margin:100px auto;
	border-radius: 10px;
	padding:30px 10px;
	list-style:none;
	border:#DFDFDF 1px solid;
}
.main p {
	line-height: 18px;
	margin: 5px 20px;
}
-->
</style>
</head>
<body>
<div class="main">
<p>$msg</p>
<p><a href="$url">&laquo;点击返回</a></p>
</div>
</body>
</html>
EOT;
		exit;
	}


	/**
	 * 时间转化函数
	 *
	 * @param $datetemp 时间戳
	 * @param $dstr 格式化字符串
	 * @return string
	 */
	public static function smartDate($datetemp, $dstr='Y-m-d H:i:s')
	{
		$timezone = 0;
		$op = '';
		$sec = time() - $datetemp;
		$hover = floor($sec / 3600);
		if ($hover == 0){
			$min = floor($sec / 60);
			if ( $min == 0) {
				$op = $sec.' 秒前';
			} else {
				$op = "$min 分钟前";
			}
		} elseif ($hover < 24){
			$op = "约 {$hover} 小时前";
		} else {
			$op = date($dstr, $datetemp);
		}
		return $op;
	}

		
	/**
	 * 获取随机日期的时间戳
	 */
	public static function getRandTime()
	{
		return rand(strtotime('2007-01-01'),time());
	}

	/**
	 * 计算脚本执行时间
	 * @param $time 由microtime(true) 生成的脚本运行时间
	 */
	public static function runTime($time)
	{
		return microtime(true) - $time;
	}

	/**
	 * 截取编码为utf8的字符串
	 *
	 * @param string $strings 预处理字符串
	 * @param int $start 开始处 eg:0
	 * @param int $length 截取长度
	 * @param int $prefix 自动链接后缀
	 */
	public static function subStr($strings,$start,$length, $prefix = '')
	{
		$str = substr($strings, $start, $length);
		$char = 0;
		for ($i = 0; $i < strlen($str); $i++){
			if (ord($str[$i]) >= 128)
			$char++;
		}
		$str2 = substr($strings, $start, $length+1);
		$str3 = substr($strings, $start, $length+2);
		if ($char % 3 == 1){
			if ($length <= strlen($strings)){
				$str3 = $str3 .= $prefix;
			}
			return $str3;
		}
		if ($char%3 == 2){
			if ($length <= strlen($strings)){
				$str2 = $str2 .= $prefix;
			}
			return $str2;
		}
		if ($char%3 == 0){
			if ($length <= strlen($strings)){
				$str = $str .= $prefix;
			}
			return $str;
		}
	}

	/**
	 * 从可能包含html标记的内容中萃取纯文本摘要
	 *
	 * @param string $data
	 * @param int $len
	 */
	public static function extractHtmlData($data, $len)
	{
		$data = strip_tags(subString($data, 0, $len + 30));
		$search = array (
			"/([\r\n])[\s]+/",	// 去掉空白字符
			"/&(quot|#34);/i",	// 替换 HTML 实体
			"/&(amp|#38);/i",
			"/&(lt|#60);/i",
			"/&(gt|#62);/i",
			"/&(nbsp|#160);/i",
			"/&(iexcl|#161);/i",
			"/&(cent|#162);/i",
			"/&(pound|#163);/i",
			"/&(copy|#169);/i",
			"/\"/i",
		);
		$replace = array (" ","\"","&"," "," ","",chr(161),chr(162),chr(163),chr(169), "");
		$data = self::subStr(preg_replace($search, $replace, $data), 0, $len);
		return $data;
	}

	/**
	 * Session 函数
	 */
	public static function Session($key, $value = 'NULL')
	{
		if($value === 'NULL')
		{
			return isset($_SESSION[$key]) ? $_SESSION[$key] : Null;
		}else
		{
			$_SESSION[$key] = $value;
		}
	}

}
