<?php
/**
 * 图片处理类
 */
class imagesLIB
{
	/**
	 * 图片生成缩略图
	 *
	 * @param string $img 预缩略的图片
	 * @param string $thum_path 生成缩略图路径
	 * @param int $max_w 缩略图最大宽度 px
	 * @param int $max_h 缩略图最大高度 px
	 * @return unknown
	 */
	public static function resizeImage($img, $thum_path, $max_w, $max_h) {
		//仅支持PNG,JPG图片的缩略
		if (!in_array(self::getFileSuffix($thum_path), array('jpg','png','jpeg'))) {
			return false;
		}
		//是否支持GD
		if (!function_exists('ImageCreate')) {
			return false;
		}

		$size = self::chImageSize($img, $max_w, $max_h);
		$newwidth = $size['w'];
		$newheight = $size['h'];
		$w = $size['rc_w'];
		$h = $size['rc_h'];
		if ($w <= $max_w && $h <= $max_h){
			return false;
		}
		return self::imageCropAndResize($img, $thum_path, 0, 0, 0, 0, $newwidth, $newheight, $w, $h);
	}

	/**
	 * 裁剪、缩放图片
	 *
	 * @param string $src_image 原始图
	 * @param string $dst_path 裁剪后的图片保存路径
	 * @param int $dst_x 新图坐标x
	 * @param int $dst_y 新图坐标y
	 * @param int $src_x 原图坐标x
	 * @param int $src_y 原图坐标y
	 * @param int $dst_w 新图宽度
	 * @param int $dst_h 新图高度
	 * @param int $src_w 原图宽度
	 * @param int $src_h 原图高度
	 */
	public static function imageCropAndResize($src_image, $dst_path, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
		$ext = strtolower(substr(strrchr($src_image, "."),1));
		if(!in_array($ext, array('jpg','png','gif')))return false;
		if(function_exists('imagecreatefromstring')){
			$src_img = imagecreatefromstring(file_get_contents($src_image));
		} else {
			return false;
		}

		if (function_exists('imagecopyresampled')){
			$new_img = imagecreatetruecolor($dst_w, $dst_h);
			imagecopyresampled($new_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		} elseif(function_exists('imagecopyresized')) {
			$new_img = imagecreate($dst_w, $dst_h);
			imagecopyresized($new_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		} else {
			return false;
		}

		switch (self::getFileSuffix($dst_path))
		{
			case 'png':
				if(function_exists('imagepng') && imagepng($new_img, $dst_path)){
					ImageDestroy ($new_img);
					return true;
				} else {
					return false;
				}
				break;
			case 'jpg':
			default:
				if(function_exists('imagejpeg') && imagejpeg($new_img, $dst_path)){
					ImageDestroy ($new_img);
					return true;
				} else {
					return false;
				}
				break;
		}
	}

	/**
	 * 按比例计算图片缩放尺寸
	 *
	 * @param string $img 图片路径
	 * @param int $max_w 最大缩放宽
	 * @param int $max_h 最大缩放高
	 * @return array
	 */
	public static function chImageSize ($img, $max_w, $max_h){
		$size = @getimagesize($img);
		$w = $size[0];
		$h = $size[1];
		//计算缩放比例
		@$w_ratio = $max_w / $w;
		@$h_ratio =	$max_h / $h;
		//决定处理后的图片宽和高
		if( ($w <= $max_w) && ($h <= $max_h) ){
			$tn['w'] = $w;
			$tn['h'] = $h;
		} else if(($w_ratio * $h) < $max_h){
			$tn['h'] = ceil($w_ratio * $h);
			$tn['w'] = $max_w;
		} else {
			$tn['w'] = ceil($h_ratio * $w);
			$tn['h'] = $max_h;
		}
		$tn['rc_w'] = $w;
		$tn['rc_h'] = $h;
		return $tn ;
	}

	/**
	 * 获取文件后缀
	 * @param string $fileName
	 */
	public static function getFileSuffix($fileName)
	{
		return strtolower(substr(strrchr($fileName, "."),1));
	}

}