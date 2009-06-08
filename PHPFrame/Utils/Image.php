<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	utils
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Image Class
 * 
 * @package		PHPFrame
 * @subpackage 	utils
 * @since 		1.0
 */
class PHPFrame_Utils_Image 
{
	function resize_image($sourcefile, 
						  $destfile, 
						  $forcedwidth=85, 
						  $forcedheight=60, 
						  $imgcomp=0) 
	{
		// $imgcomp es la compresion que le damos. 0 best quality | 100 worst quality
		$g_imgcomp = 100-$imgcomp;
		$g_srcfile = $sourcefile;
		$g_dstfile = $destfile;
		$g_fw = $forcedwidth;
		$g_fh = $forcedheight;

		if (file_exists($g_srcfile)) {
			$g_is = getimagesize($g_srcfile); // sacamos informacion sobre la imagen original
			$src_width = $g_is[0];
			$src_height = $g_is[1];
			$src_type = $g_is[2]; // 1 = GIF, 2 = JPG, 3 = PNG
			
			if (($src_width-$g_fw)>=($src_height-$g_fh)) {
				$g_iw = $g_fw;
				$g_ih = ($g_fw/$src_width)*$src_height;
			}
			else {
				$g_ih = $g_fh;
				$g_iw = ($g_ih/$src_height)*$src_width;   
			}
			
			$img_src = $this->img_create_from_file($g_srcfile, $src_type);
			$img_dst = $this->img_create($g_iw, $g_ih, $src_type);
			imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $g_iw, $g_ih, $src_width, $src_height);
			$this->img_output($img_dst, $g_dstfile, $g_imgcomp, $src_type);
			imagedestroy($img_dst);
			//$this->create_img_border($g_dstfile, $src_type); // draw border
			return true;
		}
		else
		return false;
	}
	
	function img_create_from_file($g_srcfile, $src_type) 
	{
		ini_set('memory_limit', '32M');
		switch ($src_type) {
			case 1: // for gif
				$a = imagecreatefromgif($g_srcfile);
				return $a;
			case 2: // for jpeg
				$a = imagecreatefromjpeg($g_srcfile);
				return $a;
			case 3: // for png
				$a = imagecreatefrompng($g_srcfile);
				return $a;
		}	
	}
	
	function img_create($g_iw, $g_ih, $src_type) 
	{
		switch ($src_type) {
			case 1: // for gif
				$a = imagecreate($g_iw,$g_ih);
				return $a;
			case (2 || 3): // for jpeg and png
				$a = imagecreatetruecolor($g_iw,$g_ih);
				return $a;
		}		
	}
	
	function img_output($img_dst, $g_dstfile, $g_imgcomp, $src_type) 
	{
		switch ($src_type) {
			case 1: // for gif
				imagegif($img_dst, $g_dstfile); // for gif
				break;
			case 2: // for jpeg
				imagejpeg($img_dst, $g_dstfile, $g_imgcomp); // for jpeg
				break;
			case 3: // for png
				$g_imgcomp /= 10;
				if($g_imgcomp > 9) $g_imgcomp = 9;
				imagepng($img_dst, $g_dstfile, $g_imgcomp); // for png
				break;
		}		
	}
	
	function create_img_border($img_file, $type) 
	{
     	$quality = 100;
     	$borderColor = 127;  // 255 = white
    
		/*
			a                        b
			+-------------------------+
			|                         
			|          IMAGE         
			|                         
			+-------------------------+
			c                        d 
		*/

   		$scr_img = $this->img_create_from_file($img_file, $type);
   		$width  = imagesx($scr_img);
   		$height  = imagesy($scr_img);
            
       	// line a - b
       	$abX  = 0;
       	$abY  = 0;
       	$abX1 = $width;
       	$abY1 = 0;
		
       	// line a - c
       	$acX  = 0;
       	$acY  = 0;
       	$acX1 = 0;
       	$acY1 = $height;
		
       	// line b - d
       	$bdX  = $width-1;
       	$bdY  = 0;
       	$bdX1 = $width-1;
       	$bdY1 = $height;
		
       	// line c - d
       	$cdX  = 0;
       	$cdY  = $height-1;
       	$cdX1 = $width;
       	$cdY1 = $height-1;
		
       	// DRAW LINES   
       	imageline($scr_img,$abX,$abY,$abX1,$abY1,$borderColor);
       	imageline($scr_img,$acX,$acY,$acX1,$acY1,$borderColor);
       	imageline($scr_img,$bdX,$bdY,$bdX1,$bdY1,$borderColor);
       	imageline($scr_img,$cdX,$cdY,$cdX1,$cdY1,$borderColor);
 
     	// create copy from image   
       	$this->img_output($scr_img, $img_file, $quality, $type);
       	imagedestroy($scr_img);
  }
}
