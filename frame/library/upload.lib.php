<?php
/**
 * 上传类
 */
class uploadLIB
{
	/**
	 * 文件上传
	 *
	 * @param string $fileName 文件名
	 * @param string $errorNum 错误码：$_FILES['error']
	 * @param string $tmpFile 上传后的临时文件
	 * @param string $fileSize 文件大小 KB
	 * @param array $type 允许上传的文件类型
	 * @return string 文件路径
	 */
	public static function uploadFile($fileName, $filePath, $maxsize, $errorNum, $tmpFile, $fileSize, $type)
	{
		if ($errorNum == 1){
			toolsPlugin::Msg('文件大小超过系统'.ini_get('upload_max_filesize').'限制');
		}elseif ($errorNum > 1){
			toolsPlugin::Msg('上传文件失败,错误码：'.$errorNum);
		}
		$extension  = self::getFileSuffix($fileName);
		if (!in_array($extension, $type)){

			toolsPlugin::Msg('错误的文件类型');
		}
		if ($fileSize > $maxsize){
			$ret = self::changeFileSize($maxsize);
			toolsPlugin::Msg("文件大小超出{$ret}的限制");
		}
		$uppath = $filePath . gmdate('Ym') . '/';
		$fname = md5($fileName) . gmdate('YmdHis') .'.'. $extension;
		$attachpath = $uppath . $fname;
		if (!is_dir($filePath)){
			umask(0);
			$ret = @mkdir($filePath, 0777);
			if ($ret === false){
				toolsPlugin::Msg('创建文件上传目录失败');
			}
		}
		if (!is_dir($uppath)){
			umask(0);
			$ret = @mkdir($uppath, 0777);
			if ($ret === false){
				toolsPlugin::Msg('上传失败。文件上传目录(content/uploadfile)不可写');
			}
		}
		$thum = $uppath . 'thum-' . $fname;
		$attach = $attachpath;
		if (@is_uploaded_file($tmpFile)){
			if (@!move_uploaded_file($tmpFile ,$attachpath)){
				@unlink($tmpFile);
				toolsPlugin::Msg('上传失败。文件上传目录(content/uploadfile)不可写');
			}
			chmod($attachpath, 0777);
		}
		return 	$attach;
	}

	/**
	 * 转换附件大小单位
	 *
	 * @param string $fileSize 文件大小 kb
	 */
	public static function changeFileSize($fileSize){
		if($fileSize >= 1073741824){
			$fileSize = round($fileSize / 1073741824  ,2) . 'GB';
		} elseif($fileSize >= 1048576){
			$fileSize = round($fileSize / 1048576 ,2) . 'MB';
		} elseif($fileSize >= 1024){
			$fileSize = round($fileSize / 1024, 2) . 'KB';
		} else{
			$fileSize = $fileSize . '字节';
		}
		return $fileSize;
	}

	/**
	 * 获取文件后缀
	 * @param string $fileName
	 */
	public static function getFileSuffix($fileName)
	{
		return strtolower(substr(strrchr($fileName, "."),1));
	}

	/**
	 * 获取文件url路径
	 */
	public static function getFileUrl($file, $root = '', $host = '')
	{
		return str_replace($root, $host, $file);
	}






}