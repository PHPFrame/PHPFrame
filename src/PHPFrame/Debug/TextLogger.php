<?php
/**
 * PHPFrame/Debug/TextLog.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Debug
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Text Log Class
 * 
 * @category PHPFrame
 * @package  Debug
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_TextLogger extends PHPFrame_Logger
{
    private $_log_file_info = null;
    
    /**
     * Constructor
     * 
     * @param string $file_name
     * @param int    $log_level
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($file_name, $log_level=-1)
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
        $this->_log_file_info = new PHPFrame_FileInfo($file_name);
        
        if (!$this->_log_file_info->isWritable()) {
            $msg  = "Log file ".$file_name." is not writable. ";
            $msg .= "Please check file permissions. ";
            trigger_error($msg, E_USER_ERROR);
        }
    }
    
    /**
     * Implementation of IteratorAggregate interface
     * 
     * @return Iterator
     * @since  1.0
     * @todo   This method still needs to be implemented.
     */
    public function getIterator()
    {
        return $this->_log_file_info->openFile("r");
    }
    
    /**
     * Write string to log file
     * 
     * @param string|array $msg The string to append to log file
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
        $info = "\n---\n";
        
        // Add date and time
        $info .= "[".date("Y-m-d H:i:s")."] ";
        
        // Add IP address if available
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $info .= "[ip:".$_SERVER['REMOTE_ADDR']."] ";
        } else {
            $info .= "[cli] ";
        }
        
        // Write log to filesystem using PHPFrame's utility class
        $log_file_obj = $this->_log_file_info->openFile("a");
        $log_file_obj->fwrite($info.$msg);
    }
}
