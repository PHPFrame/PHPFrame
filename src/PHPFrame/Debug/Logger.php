<?php
/**
 * PHPFrame/Debug/Logger.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Debug
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Logger Class
 * 
 * This class implements the "Observer" base class in order to subscribe to updates
 * from "observable" objects (objects of type PHPFrame_Base_Subject).
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Debug
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Base_Observer
 * @since      1.0
 */
class PHPFrame_Debug_Logger extends PHPFrame_Base_Observer
{
    private static $_instance=null;
    private $_log_file_info=null;
    private $_log_file_obj=null;
    
    /**
     * Constructor
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct()
    {
        if (defined("PHPFRAME_VAR_DIR")) {
            $log_dir = PHPFRAME_VAR_DIR;
        } else {
            require_once "PEAR/Config.php";
			$log_dir = PEAR_Config::singleton()->get('data_dir');
			$log_dir .= DS."PHPFrame";
        }
        
        $log_file = $log_dir.DS."log";
        
        // If log file doesn't exist we try to create it
        if (!is_file($log_file) && !touch($log_file)) {
            $msg = "Could not create log ";
            $msg .= "file (".$log_file."). ";
            $msg .= "Please check file permissions. ";
            trigger_error($msg, E_USER_ERROR);
        }
        
        // Instantiate file info object for log file
        $this->_log_file_info = new PHPFrame_FS_FileInfo($log_file);
        
        if (!$this->_log_file_info->isWritable()) {
            $msg = "Could not write log. ";
            $msg .= "File ".$log_file." is not writable. ";
            $msg .= "Please check file permissions. ";
            trigger_error($msg, E_USER_ERROR);
        }
        
        //$this->_log_file_obj = new PHPFrame_FS_FileObject($log_file);
    }
    
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    /**
     * Handle observed objects updates
     * 
     * @param SplSubject $subject The subject issuing the update
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function doUpdate(SplSubject $subject)
    {
        if ($subject instanceof PHPFrame_Database) {
            
        } else {
            
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
        $info = "\n\n---\n";
		
		// Add date and time
        $info .= "[".date("Y-m-d H:i:s")."] ";
		
		// Add IP address if available
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $info .= "[ip:".$_SERVER['REMOTE_ADDR']."] ";
        } else {
            $info .= "[cli] ";
        }
        
        // Write log to filesystem using PHPFrame's utility class
        $a = self::instance()->_log_file_info->openFile("a");
        var_dump($a); exit;
        //PHPFrame_Utils_Filesystem::write($log_file, $info.$msg, true);
    }
}
