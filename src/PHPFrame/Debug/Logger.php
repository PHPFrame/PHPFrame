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
    /**
     * Path to the log file in the file system
     * 
     * @var string
     */
    private static $_path=null;
    
    /**
     * Constructor
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct()
    {
        // Attach logger to observable subjects
        PHPFrame::DB()->attach($this);
    }
    
    /**
     * Handle observed objects updates
     * 
     * @param PHPFrame_Base_Subject $subject The subjuct issuing the update
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function doUpdate(PHPFrame_Base_Subject $subject)
    {
        
    }
    /**
     * Write string to log file
     * 
     * @param string $str The string to append to log file
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function write($str) 
    {
        // Add log info
        $info = "\n";
        $info .= "---";
        $info .= "\n";
        $info .= "[".date("Y-m-d H:i:s")."]";
        $info .= " [ip:".$_SERVER['REMOTE_ADDR']."]";
        $info .= " [client: ".PHPFrame::Session()->getClientName()."]";
        $info .= "\n";
        
        // Write log to filesystem using PHPFrame's utility class
        PHPFrame_Utils_Filesystem::write(config::LOG_FILE, $info.$str, true);
    }
}
