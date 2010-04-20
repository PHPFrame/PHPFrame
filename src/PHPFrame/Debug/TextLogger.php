<?php
/**
 * PHPFrame/Debug/TextLogger.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Debug
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Text based implementation of {@link PHPFrame_Logger}.
 *
 * @category PHPFrame
 * @package  Debug
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_TextLogger extends PHPFrame_Logger
{
    /**
     * Reference to fileinfo object.
     *
     * @var SplFileInfo
     */
    private $_log_file_info = null;

    /**
     * Constructor.
     *
     * @param string $file_name Absolute path to log file.
     * @param int    $log_level Log level. Possible values:
     *                          5 - success, info, notices, warnings and errors
     *                          4 - info, notices, warnings and errors
     *                          3 - notices, warnings and errors
     *                          2 - warnings and errors
     *                          1 - errors only
     *                          0 - Off
     *
     * @return void
     * @since  1.0
     */
    public function __construct($file_name, $log_level=1)
    {
        parent::__construct($file_name, $log_level);

        // If log file doesn't exist we try to create it
        if (!is_file($file_name) && !@touch($file_name)) {
            $msg = "Could not create log ";
            $msg .= "file (".$file_name."). ";
            $msg .= "Please check file permissions.";
            trigger_error($msg, E_USER_ERROR);
        }

        // Instantiate file info object for log file
        $this->_log_file_info = new SplFileInfo($file_name);

        if (!$this->_log_file_info->isWritable()) {
            $msg  = "Log file ".$file_name." is not writable. ";
            $msg .= "Please check file permissions. ";
            trigger_error($msg, E_USER_ERROR);
        }
    }

    /**
     * Magic method automatically invoked when 'serialising' object. This
     * method returns an array with the property names that need to be incuded
     * when serialising. Note that the fileinfo object is excluded.
     *
     * @return array
     * @since  1.0
     */
    public function __sleep()
    {
        return array("file_name", "log_level");
    }

    /**
     * Magic method automatically invoked when unserialising object. This
     * method acquires a new instance of the fileinfo object.
     *
     * @return void
     * @since  1.0
     */
    public function __wakeup()
    {
        $this->_log_file_info = new SplFileInfo($this->file_name);
    }

    /**
     * Implementation of IteratorAggregate interface.
     *
     * @return Iterator
     * @since  1.0
     */
    public function getIterator()
    {
        return $this->_log_file_info->openFile("r");
    }

    /**
     * Write string to log file.
     *
     * @param string|array $msg The string to append to log file.
     *
     * @return void
     * @since  1.0
     */
    public function write($msg)
    {
        // Implode msg to string if array given
        if (is_array($msg)) {
            $msg = implode("\n", $msg);
        }

        // Add log separator
        $info = "\n";

        // Add date and time
        $info .= "[".date("Y-m-d H:i:s")."] ";

        // Add IP address if available
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $info .= "[ip:".$_SERVER['REMOTE_ADDR']."] ";
        } else {
            $info .= "[cli] ";
        }

        $info .= "\n";

        // Write log to filesystem
        $log_file_obj = $this->_log_file_info->openFile("a");
        $log_file_obj->fwrite($info.$msg);
    }
}
