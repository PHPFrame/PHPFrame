<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    exception
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Exception and Error Handler Class
 * 
 * @package        PHPFrame
 * @subpackage     exception
 * @since         1.0
 */
class PHPFrame_Exception_Handler 
{
    /**
     * Initialise the error and exception handlers
     * 
     * This method encapsulates set_error_handler() and set_exception_handler().
     * 
     * @static
     * @access    public
     * @return    void
     * @since    1.0
     */
    public static function init() 
    {
        error_reporting(E_ALL & ~E_NOTICE);
        //set_error_handler(array("PHPFrame_Exception_Handler", "handleError"));
        set_exception_handler(array('PHPFrame_Exception_Handler', 'handleException'));
    }
    
    /**
     * Restore error and exception handlers to PHP defaults
     * 
     * This method encapsulates restore_error_handler() and restore_exception_handler().
     * 
     * @static
     * @access    public
     * @return    void
     * @since    1.0
     */
    public static function restore() 
    {
        restore_error_handler();
        restore_exception_handler();
    }
    
    /**
     * Error handler method
     * 
     * Handles PHP errors and converts them to exceptions.
     * 
     * @static
     * @access    public
     * @return    void
     * @since    1.0
     */
    public static function handleError($errno, $errstr, $errfile, $errline, $errcontext) 
    {
        // Throw error as custom exception
        throw new PHPFrame_Exception_Error($errstr, $errno, $errfile, $errline, $errcontext);
    }
    
    /**
     * Exceptions handler
     * 
     * Handles uncaught exceptions.
     * 
     * @static
     * @access    public
     * @return    void
     * @since    1.0
     * @todo    This method needs to decide what to do with the uncaught exceptions. Right now it simply outputs some basic info.
     */
    public static function handleException($exception) 
    {
        $str = 'Uncaught exception: '.$exception->getMessage()."\n";
        $str .= 'File: '.$exception->getFile()."\n";
        $str .= 'Line: '.$exception->getLine()."\n";
        //$str .= 'Severity: '.$exception->getSeverity()."\n";
        $str .= 'Code: '.$exception->getCode()."\n";
        $str .= $exception->getTraceAsString();
            
        if (config::DEBUG == 1) {
            echo '<pre>'.$str.'</pre>';
        }
        
        // Log the error to file
        PHPFrame_Debug_Log::write($str);
    }
}
