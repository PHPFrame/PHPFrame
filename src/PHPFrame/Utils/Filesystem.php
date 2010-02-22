<?php
/**
 * PHPFrame/Utils/Filesystem.php
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

/**
 * This class provides a set of static methods to interact with the filesystem, 
 * such as copy, move, upload and so on.
 * 
 * @category PHPFrame
 * @package  Utils
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Filesystem
{
    /**
     * Touch a file.
     * 
     * @param string $filename Absolute path to file.
     * 
     * @return void
     * @throws RuntimeException if touch fails.
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
     * @param string $source    Absolute path to origin.
     * @param string $dest      Absolute path to destination.
     * @param bool   $recursive [Optional]
     * 
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
     * @param string $origin      Absolute path to origin.
     * @param string $destination Absolute path to destination.
     * 
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
     * @param string $file Absolute path to file.
     * 
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
     * @param string $dir Absolute path to directory.
     * 
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
    
    /**
     * Check whether a directory is empty.
     * 
     * @param string $dir Absolute path to directory.
     * 
     * @return bool
     * @since  1.0
     */
    public static function isEmptyDir($dir)
    {
        return (bool) (($files = @scandir($dir)) && count($files) <= 2);
    } 
    
    /**
     * Upload file.
     * 
     * @param string $field_name      Name of input field of type file.
     * @param string $dir             Absolute path to upload dir.
     * @param string $accept          [Optional] List of accepted MIME types 
     *                                separated by commas. Default value is '*'.
     * @param int    $max_upload_size [Optional]
     * @param bool   $overwrite       [Optional]
     * 
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
            case 1 :
                $msg = "ERROR: PHP upload maximum file size exceeded!";
                break;
            case 2 :
                $msg = "ERROR: PHP maximum file size exceeded!";
                break;
            case 3 :
                $msg = "ERROR: Partial upload!";
                break;
            case 4 :
                $msg = "ERROR: No file submitted for upload!";
                break;
            }
            
            throw new RuntimeException($msg);
        }
        
        // check custom max_upload_size passed into the function
        if (!empty($max_upload_size) && $max_upload_size < $file_size) {
            $msg  = "ERROR: Maximum file size exceeded!";
            $msg .= ' max_upload_size: '.$max_upload_size;
            $msg .= ' | file_size: '.$file_size;
            throw new RuntimeException($msg);
        }
        
        // Check if file is of valid mime type
        if ($accept != "*") {
            $valid_file_types = explode(",", $accept);
            if (!in_array($file_type, $valid_file_types)) {
                $msg = "ERROR: File type not valid!";
                throw new RuntimeException($msg);
            }    
        }
        
        // Check for special chars
        $special_chars = array(
            'ñ','$','%','^','&','*','?','!','(',')','[',']','{','}',',','/','\\'
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
                $msg = "ERROR: Could not move file to destination directory!";
                throw new RuntimeException($msg);
            }
        } else {
            $msg = "ERROR: Possible file attack!".' '.$file_name;
            throw new RuntimeException($msg);
        }
        
        return new PHPFrame_FileInfo($dir.DS.$file_name);
    }
    
    /**
     * Get the operating system's temp directory path
     * 
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
