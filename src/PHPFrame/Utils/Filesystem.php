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
    private static $_test_mode = false;

    /**
     * Is test mode?
     *
     * @param bool $bool [Optional] TRUE to run in test mode. This allows file
     *                   uploads for files not uploaded via the web server.
     *
     * @return bool
     * @since  1.0
     */
    public static function testMode($bool=null)
    {
        if (!is_null($bool)) {
            self::$_test_mode = (bool) $bool;
        }

        return (bool) self::$_test_mode;
    }

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
                    if (!is_dir($dest) && !@mkdir($dest)) {
                        $msg = "Could not create directory '".$dest."'.";
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
     * @return SplFileInfo
     * @throws Exception on failure
     * @since  1.0
     */
    public static function upload(
        array $file_array,
        $dir,
        $accept="*",
        $max_upload_size=0,
        $overwrite=false
    ) {
        $keys = array("tmp_name", "name", "size", "type", "error");
        if (count(array_diff($keys, array_keys($file_array))) > 0) {
            $msg  = "file_array argument must contain the following keys: ";
            $msg .= "'".implode("', '", $keys)."'";
            throw new InvalidArgumentException($msg);
        }

        // check for generic errors first
        if ($file_array['error'] > 0) {
            switch ($file_array['error']) {
            case UPLOAD_ERR_INI_SIZE :
                $msg = "PHP upload maximum file size exceeded!";
                break;
            case UPLOAD_ERR_FORM_SIZE :
                $msg = "PHP maximum post size exceeded!";
                break;
            case UPLOAD_ERR_PARTIAL :
                $msg = "Partial upload!";
                break;
            case UPLOAD_ERR_NO_FILE :
                $msg = "No file submitted for upload!";
                break;
            case UPLOAD_ERR_NO_TMP_DIR :
                $msg = "Missing temporary folder!";
                break;
            case UPLOAD_ERR_CANT_WRITE :
                $msg = "Failed to write file to disk!";
                break;
            case UPLOAD_ERR_EXTENSION :
                $msg = "A PHP extension stopped the file upload!";
                break;
            }

            throw new RuntimeException($msg);
        }

        // check custom max_upload_size passed into the function
        if (!empty($max_upload_size) && $max_upload_size < $file_array['size']) {
            $msg  = "Maximum file size exceeded!";
            $msg .= ' max_upload_size: '.$max_upload_size;
            $msg .= ' | file_size: '.$file_array['size'];
            throw new RuntimeException($msg);
        }

        // Check that the destination directory exists
        if (!is_dir($dir)) {
            $msg = "Destination directory doesn't exist!";
            throw new InvalidArgumentException($msg);
        }

        // $file_tmp is where file went on webserver
        $file_tmp = $file_array['tmp_name'];

        // Check if file is of valid mime type
        $file_type = self::getMimeType($file_tmp);
        if ($file_type != $file_array['type']) {

        }

        if ($accept != "*") {
            $valid_file_types = explode(",", $accept);
            if (!in_array($file_type, $valid_file_types)) {
                $msg = "File type not valid!";
                throw new RuntimeException($msg);
            }
        }

        // Sanitise file name
        $file_name = self::filterFilename($file_array['name']);

        // Avoid overwriting if $overwrite is set to false
        if (!$overwrite && file_exists($dir.DS.$file_name)) {
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

        // put the file where we'd like it
        $path = $dir.DS.$file_name;
        if (!self::testMode() && !is_uploaded_file($file_tmp)) {
            $msg = "Possible file attack!".' '.$file_name;
            throw new RuntimeException($msg);
        }

        if (!move_uploaded_file($file_tmp, $path)
            && (self::testMode() && !@copy($file_tmp, $path))
        ) {
            $msg = "Could not move file to destination directory!";
            throw new RuntimeException($msg);
        }

        return array(
            "finfo"    => new SplFileInfo($dir.DS.$file_name),
            "mimetype" => $file_type
        );
    }

    /**
     * Filter dir or file name.
     *
     * @param string $str      The string to filter.
     * @param bool   $sanitise [Optional] Flag indicating whether or not to
     *                         sanitise (remove dodgy characters).
     *
     * @return string
     * @throws InvalidArgumentException
     * @since  1.0
     */
    public static function filterFilename($str, $sanitise=false)
    {
        // Disallow dot files, alias to home and root
        if (preg_match("/(^(\.|~|\/)|(\/|\\\))/", $str)) {
            $msg = "Invalid file or directory name.";
            throw new InvalidArgumentException($msg);
        }

        // Replace dodgy characters
        if ($sanitise) {
            $pattern = array("/[^ -\w\.]/");
            $replace = array("");
            return preg_replace($pattern, $replace, $str);
        }

        return $str;
    }

    /**
     * Check file type.
     *
     * @param string $fname Absolute path to file.
     *
     * @return bool|string
     * @since  1.0
     */
    public static function getMimeType($fname)
    {
        if (!is_file($fname)) {
            $msg = "File '".$fname."' doesn't exist.";
            throw new RuntimeException($msg);
        }

        if (function_exists("finfo_open")) {
            $finfo = finfo_open(FILEINFO_MIME);

            if (!$finfo) {
                return false;
            }

            $mime = finfo_file($finfo, $fname);
            finfo_close($finfo);

            $mime = strtolower($mime);
            $pattern = "/^([a-z0-9]+\/[a-z0-9\-\.]+);\s+charset=(.*)$/";
            if (!preg_match($pattern, $mime, $matches)) {
                throw new Exception("Error parsing MIME type.");
            }

            return $matches[1];

        } elseif (function_exists("mime_content_type")) {
            $mime = mime_content_type($fname);
            return $mime;
        }

        $msg  = "PHPFrame_Filesystem::getMimeType() requires either the ";
        $msg .= "php-finfo module or mime_content_type and none could be found.";
        throw new Exception($msg);
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

    /**
     * Format int representing bytes as a human readable string.
     *
     * @param int $int The number of bytes.
     *
     * @return string
     * @since  1.0
     */
    public static function bytes2human($int)
    {
        $int = (int) $int;

        if ($int < 1024) {
            return $int." bytes";
        } elseif ($int < (1024*1024)) {
            return round($int/1024, 2)."KB";
        } else {
            return round($int/(1024*1024), 2)."MB";
        }
    }
}
