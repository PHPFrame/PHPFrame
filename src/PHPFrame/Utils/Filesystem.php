<?php
/**
 * PHPFrame/Utils/Filesystem.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Utils
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Filesystem Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Utils
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Utils_Filesystem
{
    /**
     * Write string to file
     * 
     * @param string $fname   The full path to the file
     * @param string $content The content to store in the file
     * @param bool   $append  Flag to indicate whether we want to append the content. 
     *                        Default is FALSE
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public static function write($fname, $content, $append=false) 
    {
        // Set access type depending on append flag
        $mode = $append ? "a" : "w";
        
        // Open file for writing
        if (!$fhandle = fopen($fname, $mode)) {
            throw new PHPFrame_Exception_Filesystem('Error opening file '.$fname.' for writing.');
        }
        
        // Write contents into file
        if (!fwrite($fhandle, $content)) {
            throw new PHPFrame_Exception_Filesystem('Error writing file '.$fname.'.');
        }
        
        // Close file
        if (!fclose($fhandle)) {
            throw new PHPFrame_Exception_Filesystem('Error closing file '.$fname.' after writing.');
        }
    }
    
    /**
     * Ensure that directory is writable
     * 
     * @param string $path Path to directory to ensure that it is writable
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public static function ensureWritableDir($path) 
    {
        $path = (string) $path;
        $path_array = explode(DS, trim($path, DS));
        $path_prefix = DS;
        if (DS=="\\") //if the DS is backslash we are on windows, path prefix should be empty
        	$path_prefix = '';
        
        foreach ($path_array as $path_item) {
            // If dir doesnt exist we try to create it
            if (!is_dir($path_prefix.$path_item)) {
                if (!mkdir($path_prefix.$path_item, 0771)) {
                    $msg = "Could not create directory ".$path_prefix.$path_item.".";
                    throw new PHPFrame_Exception_Filesystem($msg);
                }
            }
            
            $path_prefix .= $path_item.DS;
        }
        
        if (!is_writable($path)) {
            throw new PHPFrame_Exception_Filesystem("Directory ".$path." is not writable.");
        }
    }
    
    public static function isEmptyDir($dir)
    {
        return (($files = @scandir($dir)) && count($files) <= 2);
    } 
    
    /**
     * Upload file
     * 
     * @param  string $fieldName
     * @param  string $dir
     * @param  string $accept
     * @param  int    $max_upload_size
     * @param  bool   $overwrite
     * 
     * @access public
     * @return mixed An assoc array containing file_name, file_size and file_type 
     *               or an assoc array containing error on failure.
     * @since  1.0
     */
    public static function uploadFile(
        $fieldName,
        $dir,
        $accept="*",
        $max_upload_size=0,
        $overwrite=false
    ) {
        // Get file data from request
        $file_tmp = $_FILES[$fieldName]['tmp_name']; // $file_tmp is where file went on webserver
        $file_name = $_FILES[$fieldName]['name']; // $file_tmp_name is original file name
        $file_size = $_FILES[$fieldName]['size']; // $file_size is size in bytes
        $file_type = $_FILES[$fieldName]['type']; // $file_type is mime type e.g. image/gif
        $file_error = $_FILES[$fieldName]['error']; // $file_error is any error encountered
        
        // Make sure that upload target is writable
        self::ensureWritableDir($dir);
        
        // Declare array to be used for return
        $array = array();
        
        // check for generic errors first          
        if ($file_error > 0) {
            switch ($file_error) {
              case 1:  $array['error'] = PHPFrame_Lang::UPLOAD_ERROR_PHP_UP_MAX_FILESIZE;
              case 2:  $array['error'] = PHPFrame_Lang::UPLOAD_ERROR_PHP_MAX_FILESIZE;
              case 3:  $array['error'] = PHPFrame_Lang::UPLOAD_ERROR_PARTIAL_UPLOAD;
              case 4:  $array['error'] = PHPFrame_Lang::UPLOAD_ERROR_NO_FILE;
            }
            return $array;
        }
        
        // check custom max_upload_size passed into the function
        if (!empty($max_upload_size) && $max_upload_size < $file_size) {
            $array['error'] = PHPFrame_Lang::UPLOAD_ERROR_MAX_FILESIZE;
            $array['error'] .= ' max_upload_size: '.$max_upload_size.' | file_size: '.$file_size;
            return $array;
        }
        
        // Checkeamos el MIME type con la lista que formatos validos ($accept - valores separados por ',')
        if ($accept != "*") {
            $valid_file_types = explode(",", $accept);
            $type_ok = 0;
            
            foreach ($valid_file_types as $type) {
                if ($file_type == $type) {
                    $type_ok = 1;
                }
            }
            
            if ($type_ok == 0) {
                $array['error'] = PHPFrame_Lang::UPLOAD_ERROR_FILETYPE;
                return $array;
            }    
        }
        
        // CHECK FOR SPECIAL CHARACTERS
        $special_chars = array('á','ä','à','é','ë','è','í','ï','ì','ó','ö','ò','ú','ü','ù','Á','Ä','À','É','Ë','È','Í','Ï','Ì','Ó','Ö','Ò','Ú','Ü','Ù','ñ','Ñ','?','¿','!','¡','(',')','[',']',',');
        foreach ($special_chars as $special_char) {
            $file_name = str_replace($special_char, '', $file_name);
        }
        
        // BEFORE WE MOVE THE FILE TO IT'S TARGET DIRECTORY 
        // WE CHECK IF A FILE WITH THE SAME NAME EXISTS IN THE TARGET DIRECTORY
        if ($overwrite === false) {
          $check_if_file_exists = file_exists($dir.DS.$file_name);
          if ($check_if_file_exists === true) {
            // split file name into name and extension
            $split_point = strrpos($file_name, '.');
            $file_n = substr($file_name, 0, $split_point);
            $file_ext = substr($file_name, $split_point);
            $i=0;
            while (true === file_exists($dir.DS.$file_n.$i.$file_ext)) {
                $i++;
            }
            $file_name = $file_n.$i.$file_ext;
          }
        }
        
        // put the file where we'd like it
        $path = $dir.DS.$file_name;
        // is_uploaded_file and move_uploaded_file added at version 4.0.3
        if (is_uploaded_file($file_tmp)) {
            if (!move_uploaded_file($file_tmp, $path)) {
                $array['error'] = PHPFrame_Lang::UPLOAD_ERROR_MOVE;
                return $array;
            }
        } 
        else {
            $array['error'] = PHPFrame_Lang::UPLOAD_ERROR_ATTACK.' '.$file_name;
            return $array;
        }
        
        $array = array('file_name' => $file_name, 'file_size' => $file_size, 'file_type' => $file_type, 'error' => '');
        return $array;
    }
}
