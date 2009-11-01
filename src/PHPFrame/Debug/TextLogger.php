<?php
/**
 * PHPFrame/Debug/TextLog.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Debug
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Text Log Class
 * 
 * @category PHPFrame
 * @package  Debug
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_TextLog extends PHPFrame_Log
{
    private $_log_file_info = null;
    private $_log_file_obj = null;
    
    /**
     * Constructor
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct()
    {
        if (defined("PHPFRAME_TMP_DIR")) {
            $log_dir = PHPFRAME_TMP_DIR;
        } else {
            $log_dir  = PEAR_Config::singleton()->get('data_dir');
            $log_dir .= DS."PHPFrame";
        }
        
        $log_file = $log_dir.DS."log";
        
        // If log file doesn't exist we try to create it
        if (!is_file($log_file) && !@touch($log_file)) {
            $msg = "Could not create log ";
            $msg .= "file (".$log_file."). ";
            $msg .= "Please check file permissions. Error triggered ";
            trigger_error($msg, E_USER_ERROR);
        }
        
        // Instantiate file info object for log file
        $this->_log_file_info = new PHPFrame_FileInfo($log_file);
        
        if (!$this->_log_file_info->isWritable()) {
            $msg = "Could not write log. ";
            $msg .= "File ".$log_file." is not writable. ";
            $msg .= "Please check file permissions. ";
            trigger_error($msg, E_USER_ERROR);
        }
    }
    
    /**
     * Handle observed objects updates
     * 
     * @param PHPFrame_Subject $subject The subject issuing the update
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function doUpdate(PHPFrame_Subject $subject)
    {
        list($msg, $type) = $subject->getLastEvent();
        
        $log_level = PHPFrame::Config()->get("debug.log_level");
        if ($type <= $log_level) {
            self::write($msg);
        }
    }
    
    /**
     * Write string to log file
     * 
     * @param string|array $msg The string to append to log file
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function write($msg) 
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
        $log_file_obj = self::instance()->_log_file_info->openFile("a");
        $log_file_obj->fwrite($info.$msg);
    }
}