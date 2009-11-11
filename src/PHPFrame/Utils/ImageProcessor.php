<?php
/**
 * PHPFrame/Utils/ImageProcessor.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Utils
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class creates objects used to process images. So far the only thing 
 * it does is resize.
 * 
 * @category PHPFrame
 * @package  Utils
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_ImageProcessor
{
    /**
     * Resize image
     * 
     * @param string $src_filename
     * @param string $dst_filename
     * @param int    $max_width
     * @param int    $max_height
     * @param int    $imgcomp       0 best quality | 100 worst quality
     * 
     * @access public
     * @return void
     * @throws Exception on failure
     * @since  1.0
     */
    public function resize(
        $src_filename, 
        $dst_filename,
        $max_width=85,
        $max_height=60,
        $imgcomp=0
    ) 
    {
        $imgcomp = (100 - $imgcomp);

        if (!file_exists($src_filename)) {
            $msg = "Can not resize image. File doesn't exist";
            throw new RuntimeException($msg);
        } else {
            $size_array = getimagesize($src_filename);
            $src_width  = $size_array[0];
            $src_height = $size_array[1];
            $src_type   = $size_array[2]; // 1 = GIF, 2 = JPG, 3 = PNG
            
            if (($src_width-$max_width) >= ($src_height-$max_height)) {
                $dst_width  = $max_width;
                $dst_height = ($max_width/$src_width)*$src_height;
            } else {
                $dst_height = $max_height;
                $dst_width  = ($dst_height/$src_height)*$src_width;   
            }
            
            $src_img = $this->_createFromFile($src_filename, $src_type);
            $dst_img = $this->_create($dst_width, $dst_height, $src_type);
            
            imagecopyresampled(
                $dst_img, 
                $src_img, 
                0, 
                0, 
                0, 
                0, 
                $dst_width, 
                $dst_height, 
                $src_width, 
                $src_height
            );
            
            $this->_output($dst_img, $dst_filename, $imgcomp, $src_type);
            
            imagedestroy($dst_img);
            
            //$this->_drawBorder($dst_filename, $src_type);
        }
    }
    
    private function _createFromFile($src_filename, $src_type) 
    {
        ini_set('memory_limit', '32M');
        
        switch ($src_type) {
            case 1: // for gif
                return imagecreatefromgif($src_filename);
            case 2: // for jpeg
                return imagecreatefromjpeg($src_filename);
            case 3: // for png
                return imagecreatefrompng($src_filename);
        }    
    }
    
    private function _create($dst_width, $dst_height, $src_type) 
    {
        switch ($src_type) {
            case 1: // for gif
                return imagecreate($dst_width,$dst_height);
            case (2 || 3): // for jpeg and png
                return imagecreatetruecolor($dst_width,$dst_height);
        }        
    }
    
    private function _output($dst_img, $dst_filename, $imgcomp, $src_type) 
    {
        switch ($src_type) {
            case 1: // for gif
                imagegif($dst_img, $dst_filename); // for gif
                break;
            case 2: // for jpeg
                imagejpeg($dst_img, $dst_filename, $imgcomp); // for jpeg
                break;
            case 3: // for png
                $imgcomp /= 10;
                if ($imgcomp > 9) $imgcomp = 9;
                imagepng($dst_img, $dst_filename, $imgcomp); // for png
                break;
        }        
    }
    
    private function _drawBorder($img_file, $type, $colour=127, $quality=100) 
    {    
        /*
            a                        b
            +-------------------------+
            |                         
            |          IMAGE         
            |                         
            +-------------------------+
            c                        d 
        */

       $scr_img = $this->_createFromFile($img_file, $type);
       $width   = imagesx($scr_img);
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
       imageline($scr_img,$abX,$abY,$abX1,$abY1,$colour);
       imageline($scr_img,$acX,$acY,$acX1,$acY1,$colour);
       imageline($scr_img,$bdX,$bdY,$bdX1,$bdY1,$colour);
       imageline($scr_img,$cdX,$cdY,$cdX1,$cdY1,$colour);
 
       // create copy from image   
       $this->_output($scr_img, $img_file, $quality, $type);
       
       imagedestroy($scr_img);
    }
}
