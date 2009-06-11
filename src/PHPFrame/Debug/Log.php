<?php
/**
 * PHPFrame/Debug/Log.php
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
 * Log Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Debug
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Debug_Log
{
    /**
     * Write string to log file
     * 
     * @static
     * @access    public
     * @param    string    $str    The string to append to log file
     * @return    void
     */
    public static function write($str) 
    {
        // Add log info
        $info = "\n";
        $info .= "---";
        $info .= "\n";
        $info .= "[".date("Y-m-d H:i:s")."]";
        $info .= " [ip:".$_SERVER['REMOTE_ADDR']."]";
        $info .= " [client: ".PHPFrame::getSession()->getClientName()."]";
        $info .= "\n";
        
        // Write log to filesystem using PHPFrame's utility class
        PHPFrame_Utils_Filesystem::write(config::LOG_FILE, $info.$str, true);
    }
}
