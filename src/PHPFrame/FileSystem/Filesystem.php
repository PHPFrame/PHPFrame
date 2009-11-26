<?php
/**
 * PHPFrame/FileSystem/Filesystem.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   FileSystem
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * This class provides a set of static methods to interact with the filesystem, 
 * such as copy, move, upload and so on.
 * 
 * @category PHPFrame
 * @package  FileSystem
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_Filesystem
{
    /**
     * Touch a file
     * 
     * @param string $filename
     * 
     * @static
     * @access public
     * @return void
     * @throws RuntimeException if touch fails
     * @since  1.0
     */
    public static function touch($filename)
    {
        if (!touch($filename)) {
            $msg = "Could not touch file ".$filename;
            throw new RuntimeException($msg);
        }
    }
    
    /**
     * Copy file
     * 
     * @param string $source
     * @param string $dest
     * @param bool   $recursive
     * 
     * @static
     * @access public
     * @return void
     * @throws RuntimeException if copy fails
     * @since  1.0
     * @todo   handle recursive copy
     */
    public static function cp($source, $dest, $recursive=false)
    {
        if (is_dir($source) && !$recursive) {
            $msg  = $source." is a directory. To copy directories pass third ";
            $msg .= " argument 'recursive' with a value of TRUE."; 
            throw new LogicException($msg);
        } elseif (is_dir($source) && $recursive) {
            $dir_it = new RecursiveDirectoryIterator($source);
            //print_r($dir_it);
            echo "FIX ME!! Im in ".__FILE__." line ".__LINE__;
            exit;
        } else {
            $array = explode(DS, $dest);
            array_pop($array);
            $dest_dir = implode(DS, $array);
            PHPFrame_Filesystem::ensureWritableDir($dest_dir);
            if (!copy($source, $dest)) {
                $msg = "Could not copy '".$source."' to '".$dest."'";
                throw new RuntimeException($msg);
            }
        }
    }
    
    /**
     * Move/rename file
     * 
     * @param string $origin
     * @param string $destination
     * 
     * @static
     * @access public
     * @return void
     * @throws RuntimeException if move fails
     * @since  1.0
     * @todo   method not implemented
     */
    public static function mv($origin, $destination)
    {
        echo "FIX ME!! Im in ".__FILE__." line ".__LINE__;
    }
    
    /**
     * Remove file
     * 
     * @param string $file
     * 
     * @static
     * @access public
     * @return void
     * @throws RuntimeException if move fails
     * @since  1.0
     * @todo   method not implemented
     */
    public static function rm($file)
    {
        echo "FIX ME!! Im in ".__FILE__." line ".__LINE__;
    }
    
    /**
     * List directory contents
     * 
     * @param string $dir
     * 
     * @static
     * @access public
     * @return Iterator
     * @throws RuntimeException if move fails
     * @since  1.0
     * @todo   method not implemented
     */
    public static function ls($dir)
    {
        echo "FIX ME!! Im in ".__FILE__." line ".__LINE__;
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
        
        //if the DS is backslash we are on windows, path prefix should be empty
        if (DS=="\\") {
            $path_prefix = '';
        }
        
        foreach ($path_array as $path_item) {
            // If dir doesnt exist we try to create it
            if (!is_dir($path_prefix.$path_item)) {
                if (!mkdir($path_prefix.$path_item, 0771)) {
                    $msg  = "Could not create directory ";
                    $msg .= $path_prefix.$path_item.".";
                    throw new RuntimeException($msg);
                }
            }
            
            $path_prefix .= $path_item.DS;
        }
        
        if (!is_writable($path)) {
            $msg = "Directory ".$path." is not writable.";
            throw new RuntimeException($msg);
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
     * @return PHPFrame_FileInfo
     * @throws Exception on failure
     * @since  1.0
     */
    public static function uploadFile(
        $field_name,
        $dir,
        $accept="*",
        $max_upload_size=0,
        $overwrite=false
    ) {
        // Get file data from request
        $files = PHPFrame::Request()->getFiles();
        
        if (!isset($files[$field_name])) {
            $msg  = "Can not upload file. ";
            $msg .= "File field '".$field_name."' not found in request.";
            throw new RuntimeException($msg);
        }
        
        // $file_tmp is where file went on webserver
        $file_tmp   = $files[$field_name]['tmp_name'];
        // $file_tmp_name is original file name
        $file_name  = $files[$field_name]['name'];
        // $file_size is size in bytes 
        $file_size  = $files[$field_name]['size'];
        // $file_type is mime type e.g. image/gif
        $file_type  = $files[$field_name]['type'];
        // $file_error is any error encountered
        $file_error = $files[$field_name]['error'];
        
        // check for generic errors first          
        if ($file_error > 0) {
            switch ($file_error) {
              case 1: $msg = PHPFrame_Lang::UPLOAD_ERROR_PHP_UP_MAX_FILESIZE;
              case 2: $msg = PHPFrame_Lang::UPLOAD_ERROR_PHP_MAX_FILESIZE;
              case 3: $msg = PHPFrame_Lang::UPLOAD_ERROR_PARTIAL_UPLOAD;
              case 4: $msg = PHPFrame_Lang::UPLOAD_ERROR_NO_FILE;
            }
            
            throw new RuntimeException($msg);
        }
        
        // check custom max_upload_size passed into the function
        if (!empty($max_upload_size) && $max_upload_size < $file_size) {
            $msg  = PHPFrame_Lang::UPLOAD_ERROR_MAX_FILESIZE;
            $msg .= ' max_upload_size: '.$max_upload_size;
            $msg .= ' | file_size: '.$file_size;
            throw new RuntimeException($msg);
        }
        
        // Check if file is of valid mime type
        if ($accept != "*") {
            $valid_file_types = explode(",", $accept);
            if (!in_array($file_type, $valid_file_types)) {
                $msg = PHPFrame_Lang::UPLOAD_ERROR_FILETYPE;
                throw new RuntimeException($msg);
            }    
        }
        
        // Check for special chars
        $special_chars = array(
            '�','$','%','^','&','*','?','!','(',')','[',']','{','}',',','/','\\'
        );
        foreach ($special_chars as $special_char) {
            $file_name = str_replace($special_char, '', $file_name);
        }
        
        // Avoid overwriting if $overwrite is set to false
        if ($overwrite === false) {
          $check_if_file_exists = file_exists($dir.DS.$file_name);
          if ($check_if_file_exists === true) {
            // split file name into name and extension
            $split_point = strrpos($file_name, '.');
            $file_n      = substr($file_name, 0, $split_point);
            $file_ext    = substr($file_name, $split_point);
            $i=0;
            while (true === file_exists($dir.DS.$file_n.$i.$file_ext)) {
                $i++;
            }
            $file_name = $file_n.$i.$file_ext;
          }
        }
        
        // put the file where we'd like it
        $path = $dir.DS.$file_name;
        if (is_uploaded_file($file_tmp)) {
            if (!move_uploaded_file($file_tmp, $path)) {
                $msg = PHPFrame_Lang::UPLOAD_ERROR_MOVE;
                throw new RuntimeException($msg);
            }
        } else {
            $msg = PHPFrame_Lang::UPLOAD_ERROR_ATTACK.' '.$file_name;
            throw new RuntimeException($msg);
        }
        
        return new PHPFrame_FileInfo($dir.DS.$file_name);
    }
    
    /**
     * Get the operating system's temp directory path
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public static function getSystemTempDir()
    {
        $tmp_dir = sys_get_temp_dir();
        
        // Remove trailing slash if provided
        // We do this to enforce consistent behaviour across systems
        if (preg_match('/(.+)\/$/', $tmp_dir, $matches)) {
            return $matches[1];
        }
        
        return $tmp_dir;
    }
}
