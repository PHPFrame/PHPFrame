<?php
/**
 * PHPFrame/Utils/ImageProcessor.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Utils
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

if (!function_exists("gd_info")) {
    $msg  = "PHPFrame_ImageProcessor requires the GD extension for PHP. For ";
    $msg .= "more info check the PHP <a href=\"http://www.php.net/manual/en/";
    $msg .= "book.image.php\">documentation</a>.";
    throw new Exception($msg);
}

/**
 * This class creates objects used to process images. So far the only thing
 * it does is resize.
 *
 * @category PHPFrame
 * @package  Utils
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_ImageProcessor
{
    private $_max_width = 85;
    private $_max_height = 60;
    private $_imgcomp = 0;
    private $_messages = array();
    private $_verbose = false;

    /**
     * Constructor.
     *
     * @param int  $max_width  [Optional] Default max_width.
     * @param int  $max_height [Optional] Default max_height.
     * @param int  $imgcomp    [Optional] Default image compression.
     * @param bool $verbose    [Optional] Whether to log all messages instead of
     *                         only errors in the internal messages array.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(
        $max_width=85,
        $max_height=60,
        $imgcomp=0,
        $verbose=false
    ) {
        $this->_max_width = (int) $max_width;
        $this->_max_height = (int) $max_height;
        $this->_imgcomp = (int) $imgcomp;
        $this->_verbose = (bool) $verbose;
    }

    /**
     * Resize image
     *
     * @param string|array $src_filename Absolute path to source image.
     * @param string|array $dst_filename Absolute path to destination image.
     * @param int          $max_width    [Optional] Maximum allowed width in
     *                                   pixels. Default value is 85.
     * @param int          $max_height   [Optional] Maximum allowed height in
     *                                   pixels. Default value is 60.
     * @param int          $imgcomp      [Optional] 0 best quality, 100 worst
     *                                   quality.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     * @throws Exception on failure
     * @since  1.0
     */
    public function resize(
        $src_filename,
        $dst_filename,
        $max_width=null,
        $max_height=null,
        $imgcomp=null
    ) {
        if (is_string($src_filename)) {
            $src_filename = array($src_filename);
        } elseif (!is_array($src_filename)) {
            $msg = "Source file name parameter must be either a string with ";
            $msg .= "the absolute path to the image or an array containing ";
            $msg .= "paths to many images.";
            throw new InvalidArgumentException($msg);
        }

        if (is_string($dst_filename)) {
            $dst_filename = array($dst_filename);
        } elseif (!is_array($src_filename)) {
            $msg = "Destination file name parameter must be either a string with ";
            $msg .= "the absolute path to the resized image or an array ";
            $msg .= "containing many paths.";
            throw new InvalidArgumentException($msg);
        }

        foreach (array("max_width", "max_height", "imgcomp") as $option) {
            if (!is_null($$option)) {
                $$option = (int) $$option;
            } else {
                $prop_name = "_".$option;
                $$option = $this->$prop_name;
            }
        }

        $imgcomp = (100 - $imgcomp);

        foreach ($src_filename as $key=>$source) {
            if (!file_exists($source)) {
                $msg = "Can not resize image. File '".$source."' doesn't exist.";
                throw new RuntimeException($msg);
            }

            $resize_ok = $this->_resizeImage(
                $source,
                $dst_filename[$key],
                $max_width,
                $max_height,
                $imgcomp
            );

            if (!$resize_ok) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get messages array.
     *
     * @return array
     * @since  1.0
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Get/set verbose operation.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function verbose($bool=null)
    {
        if (!is_null($bool)) {
            $this->_verbose = (bool) $bool;
        }

        return $this->_verbose;
    }

    /**
     * Estimate the amount of memory it will be needed to process the given
     * images.
     *
     * @param array $images       Array containing absolute paths to image files.
     * @param float $fudge_factor [Optional] Default value is 1.8. This default
     *                            value is the result of experimentation in my
     *                            development environment. In your own environment
     *                            you may need to tweak it, so experiment as
     *                            needed. In order to experiment with the fudge
     *                            factor you may use the provided
     *                            calculateFudgeFactor() method.
     *
     * @return int The amount of memory in bytes.
     * @see    PHPFrame_ImageProcessor::calculateFudgeFactor()
     * @since  1.0
     */
    public function estimateMemoryAllocation(array $images, $fudge_factor=1.8)
    {
        $mem = 0;

        foreach ($images as $image) {
            if (!is_file($image)) {
                $msg = "File ".$image." does not exist!";
                throw new RuntimeException($msg);
            }

            $info = getimagesize($image);

            if (!array_key_exists("channels", $info)) {
                $info["channels"] = 3;
            }

            $bpp = ($info["bits"]/8) * $info["channels"];

            $mem += ($info[0] * $info[1] * $bpp * $fudge_factor);
        }

        return (int) $mem;
    }

    /**
     * Calculate fudge factor based on a sample set of images. This method will
     * need to create the images in memory in order to figure out the fudge
     * factor so it shouldn't be called to determine it before invoking
     * {@link PHPFrame_ImageProcessor::estimateMemoryAllocation()} if you are
     * trying to avoid a memory allocation error.
     *
     * This method can be used to determine the fudge factor based on a known
     * set of images.
     *
     * @param array $images Array containing the absolute paths to the images.
     *
     * @return float The 'mean fudge factor'.
     * @since  1.0
     */
    public function calculateFudgeFactor(array $images)
    {
        $start_mem = memory_get_usage();
        $ffs = array();

        foreach ($images as $image) {
            $info     = getimagesize($image);
            $im       = $this->_createFromFile($image, $info[2]);
            $mem_diff = (memory_get_usage() - $start_mem);

            if (!array_key_exists("channels", $info)) {
                $info["channels"] = 3;
            }

            $bpp = ($info["bits"]/8) * $info["channels"];

            $ff = ($mem_diff / ($info[0] * $info[1] * $bpp));
            $ffs[] = $ff;

            imagedestroy($im);
            $start_mem = memory_get_usage();
        }

        return (array_sum($ffs) / count($ffs));
    }

    /**
     * Resize a single image.
     *
     * @param string $src_filename Absolute path to source image.
     * @param string $dst_filename Absolute path to destination image.
     * @param int    $max_width    [Optional] Maximum allowed width in pixels.
     *                             Default value is 85.
     * @param int    $max_height   [Optional] Maximum allowed height in pixels.
     *                             Default value is 60.
     * @param int    $imgcomp      [Optional] 0 best quality, 100 worst quality.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     * @since  1.0
     */
    private function _resizeImage(
        $src_filename,
        $dst_filename,
        $max_width,
        $max_height,
        $imgcomp
    ) {
        if (!preg_match("/^(\d+)M$/", ini_get("memory_limit"), $matches)) {
            $msg = "Could not parse memory_limit from PHP configuration.";
            throw new RuntimeException($msg);
        }

        $memory_limit = ((int) $matches[1]) * 1024 * 1024;
        $needed_memory = $this->estimateMemoryAllocation(array($src_filename));
        $available_memory = $memory_limit - memory_get_usage() - (1024*1024*8);

        if ($needed_memory > $available_memory) {
            $msg  = "Image resizing halted to avoid running out of memory. ";
            $msg .= "PHP memory limit is set to ".ini_get("memory_limit").". ";
            $msg .= "To avoid this problem you can increase the memory limit ";
            $msg .= "by changing this setting in your php.ini file";
            $this->_messages[] = $msg;
            return false;
        }

        $size_array = getimagesize($src_filename);
        $src_width  = $size_array[0];
        $src_height = $size_array[1];
        $src_type   = $size_array[2]; // 1 = GIF, 2 = JPG, 3 = PNG

        if (($src_width - $max_width) >= ($src_height - $max_height)) {
            $dst_width  = $max_width;
            $dst_height = ($max_width/$src_width) * $src_height;
        } else {
            $dst_height = $max_height;
            $dst_width  = ($dst_height/$src_height)*$src_width;
        }

        $src_img = $this->_createFromFile($src_filename, $src_type);
        $dst_img = $this->_create($dst_width, $dst_height, $src_type);

        $resample_ok = @imagecopyresampled(
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

        if (!$resample_ok) {
            $msg = "Error resampling image '".$src_filename."'.";
            $this->_messages[] = $msg;
            return false;
        }

        $this->_output($dst_img, $dst_filename, $imgcomp, $src_type);

        imagedestroy($dst_img);

        if ($this->verbose()) {
            $msg  = "Image '".$src_filename."' resized successfully and stored as ";
            $msg .= "'".$dst_filename."'.";
            $this->_messages[] = $msg;
        }

        return true;
    }

    /**
     * Create image from file.
     *
     * @param string $src_filename Absolute path to source image.
     * @param int    $src_type     Image type. 1 = gif, 2 = jpeg, 3 = png.
     *
     * @return resource An image resource identifier on success, false on error.
     * @since  1.0
     */
    private function _createFromFile($src_filename, $src_type)
    {
        switch ($src_type) {
        case 1: // for gif
            return imagecreatefromgif($src_filename);
        case 2: // for jpeg
            return imagecreatefromjpeg($src_filename);
        case 3: // for png
            return imagecreatefrompng($src_filename);
        }
    }

    /**
     * Create image.
     *
     * @param int $dst_width  Width of new image.
     * @param int $dst_height Height of new image.
     * @param int $src_type   Image type. 1 = gif, 2 = jpeg, 3 = png.
     *
     * @return resource An image resource identifier on success, false on error.
     * @since  1.0
     */
    private function _create($dst_width, $dst_height, $src_type)
    {
        switch ($src_type) {
        case 1: // for gif
            return imagecreate($dst_width, $dst_height);
        case (2 || 3): // for jpeg and png
            return imagecreatetruecolor($dst_width, $dst_height);
        }
    }

    /**
     * Output image to file.
     *
     * @param resource $dst_img      Absolute path to destination image.
     * @param string   $dst_filename Absolute path to destination image.
     * @param int      $imgcomp      0 best quality, 100 worst quality.
     * @param int      $src_type     Image type. 1 = gif, 2 = jpeg, 3 = png.
     *
     * @return void
     * @since  1.0
     */
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
            if ($imgcomp > 9) {
                $imgcomp = 9;
            }
            imagepng($dst_img, $dst_filename, $imgcomp); // for png
            break;
        }
    }
}
