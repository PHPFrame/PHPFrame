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
     * Copy file or directory.
     *
     * When copying directorties, the trailing slash at the end of the source
     * path will determine whether we copy the whole directory (including the
     * directory itself) or only its contents into the desination directory.
     *
     * <code>
     * // This will copy the contents of /tmp/test1 into /tmp/test2.
     * PHPFrame_Filesystem::cp("/tmp/test1/" "/tmp/test2/", true);
     * </code>
     *
     * <code>
     * // This will copy /tmp/test1 into /tmp/test2 resulting in
     * // /tmp/test2/test1.
     * PHPFrame_Filesystem::cp("/tmp/test1" "/tmp/test2", true);
     *
     * // This is the same as above, the trailing slash in the destination
     * // doesn't affect the behaviour
     * PHPFrame_Filesystem::cp("/tmp/test1" "/tmp/test2/", true);
     * </code>
     *
     * @param string $source    Absolute path to origin.
     * @param string $dest      Absolute path to destination.
     * @param bool   $recursive [Optional] Default value is false.
     *
     * @return void
     * @throws RuntimeException if copy fails
     * @since  1.0
     */
    public static function cp($source, $dest, $recursive=false)
    {
        if (!is_readable($source)) {
            $msg  = "Can not copy '".$source."'. File or directory is not ";
            $msg .= "readable.";
            throw new RuntimeException($msg);
        }

        if (is_dir($dest)) {
            if (!is_writable($dest)) {
                $msg  = "Can not copy to '".$dest."'. File or directory is not ";
                $msg .= "writable.";
                throw new RuntimeException($msg);
            }

            // If dest exists and is a dir we append source file or dir name
            $dest .= DS.end(explode(DS, $source));
        }

        if (is_dir($source) && !$recursive) {
            $msg  = $source." is a directory. To copy directories pass third ";
            $msg .= " argument 'recursive' with a value of TRUE.";
            throw new LogicException($msg);

        } elseif (is_dir($source)) {
            foreach (new DirectoryIterator($source) as $finfo) {
                if ($finfo->isDot()) {
                    continue;

                } elseif ($finfo->isDir()) {
                    self::cp(
                        $finfo->getRealPath(),
                        $dest.DS.$finfo->getFilename(),
                        true
                    );

                } else {
                    if (!is_dir($dest) && !mkdir($dest)) {
                        $msg = "Could not create directory '".$new_dir."'.";
                        throw new RuntimeException($msg);
                    }

                    self::cp($finfo->getRealPath(), $dest);
                }
            }

        } else {
            if (!copy($source, $dest)) {
                $msg = "Could not copy '".$source."' to '".$dest."'";
                throw new RuntimeException($msg);
            }
        }
    }

    /**
     * Remove file or directory.
     *
     * @param string $file      Absolute path to file or directory.
     * @param bool   $recursive [Optional] Default value is FALSE.
     *
     * @return void
     * @throws InvalidArgumentException, RuntimeException
     * @since  1.0
     */
    public static function rm($file, $recursive=false)
    {
        if ($recursive && is_dir($file)) {
            foreach (new DirectoryIterator($file) as $finfo) {
                if ($finfo->isDot()) {
                    continue;
                } elseif ($finfo->isDir()) {
                    self::rm($finfo->getRealPath(), true);
                } else {
                    self::rm($finfo->getRealPath());
                }
            }

            if (is_dir($file)) {
                if (!rmdir($file)) {
                    $msg  = "Can not delete directory '".$file."'. Please check ";
                    $msg .= "file permissions.";
                    throw new RuntimeException($msg);
                }
            }
        }

        if (is_dir($file)) {
            $msg = "Can not remove '".$file."'. It is a directory! ";
            $msg .= "To delete directories and their contents set the ";
            $msg .= "'\$recursive' argument to TRUE when calling ";
            $msg .= "PHPFrame_Filesystem::".__FUNCTION__."().";
            throw new InvalidArgumentException($msg);
        }

        if (is_file($file)) {
            if (!unlink($file)) {
                $msg  = "Can not delete file '".$file."'. Please check file ";
                $msg .= "permissions.";
                throw new RuntimeException($msg);
            }
        }
    }

    /**
     * Ensure that directory is writable.
     *
     * @param string $path Path to directory to ensure that it is writable.
     *
     * @return void
     * @since  1.0
     */
    public static function ensureWritableDir($path)
    {
        $path        = (string) $path;
        $path_array  = explode(DS, trim($path, DS));
        $path_prefix = DS;

        //if the DS is backslash we are on windows, path prefix should be empty
        if (DS == "\\") {
            $path_prefix = '';
        }

        foreach ($path_array as $path_item) {
            // If dir doesnt exist we try to create it
            if (!is_dir($path_prefix.$path_item)) {
                if (@!mkdir($path_prefix.$path_item, 0771)) {
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
     * <code>
     * // We assume that a form was posted containing a file field named
     * // 'form_file_field'
     * $file = PHPFrame_Filesystem::upload(
     *     $request()->file("form_file_field"),
     *     "/some/dir/"
     * );
     * </code>
     *
     * @param string $file_array      An associative array with the posted file
     *                                details. This is normally taken from the
     *                                request.
     *                                - tmp_name
     *                                - name
     *                                - size
     *                                - type
     *                                - error
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
    public static function upload(
        $file_array,
        $dir,
        $accept="*",
        $max_upload_size=0,
        $overwrite=false
    ) {
        // $file_tmp is where file went on webserver
        $file_tmp   = $file_array['tmp_name'];
        // $file_tmp_name is original file name
        $file_name  = $file_array['name'];
        // $file_size is size in bytes
        $file_size  = $file_array['size'];
        // $file_type is mime type e.g. image/gif
        $file_type  = $file_array['type'];
        // $file_error is any error encountered
        $file_error = $file_array['error'];

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

    /**
     * Get the operating system's user home directory path
     *
     * @return string
     * @since  1.0
     */
    public static function getUserHomeDir()
    {
        if (array_key_exists("HOME", $_SERVER)) {
            return $_SERVER["HOME"];
        } elseif (array_key_exists("HOMEPATH", $_SERVER)) {
            return $_SERVER["HOMEPATH"];
        } else {
            $msg = "Could not determine path to user home directory.";
            throw new RuntimeException($msg);
        }
    }
}
