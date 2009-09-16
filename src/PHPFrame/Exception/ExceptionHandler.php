<?php
/**
 * PHPFrame/Exception/ExceptionHandler.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Exception
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Exception and Error Handler Class
 * 
 * @category PHPFrame
 * @package  Exception
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_ExceptionHandler extends PHPFrame_Subject
{
    /**
     * Propery holding single instance of this class
     * 
     * @var PHPFrame_ExceptionHandler
     */
    private static $_instance = null;
    
    /**
     * We declare a private constructor to avoid instantiation from client code 
     * enforce the singleton pattern.
     * 
     * The constructor initialises the error and exception handlers
     * 
     * This method encapsulates set_error_handler() and set_exception_handler().
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct()
    {
        //error_reporting(E_ALL | E_NOTICE | E_STRICT);
        error_reporting(E_ALL);
        //set_error_handler(array("PHPFrame_ExceptionHandler", "handleError"));
        set_exception_handler(array('PHPFrame_ExceptionHandler','handleException'));
    }
    
    /**
     * Get singleton instance of PHPFrame_ExceptionHandler
     * 
     * @access public
     * @return PHPFrame_ExceptionHandler
     * @since  1.0
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    /**
     * Restore error and exception handlers to PHP defaults
     * 
     * This method encapsulates restore_error_handler() and 
     * restore_exception_handler().
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
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
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @param string $errcontext
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function handleError(
        $errno, 
        $errstr, 
        $errfile, 
        $errline, 
        $errcontext
    )
    {
        // Throw error as custom exception
        throw new PHPFrame_ErrorException(
            $errstr, 
            $errno, 
            $errfile, 
            $errline, 
            $errcontext
        );
    }
    
    /**
     * Exceptions handler
     * 
     * Handles uncaught exceptions.
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function handleException($exception) 
    {
        $str  = "Uncaught ".get_class($exception).": ";
        $str .= $exception->getMessage()."\n";
        $str .= "File: ".$exception->getFile()."\n";
        $str .= "Line: ".$exception->getLine()."\n";
        $str .= "Code: ".$exception->getCode()."\n";
        $str .= $exception->getTraceAsString();
        
        // Display the exception details if debugging is enabled
        $config = PHPFrame::Config();
        $display_exceptions = $config->get("debug.display_exceptions");
        if ($display_exceptions) {
            if (PHPFrame::Session()->getClientName() == "default") {
                $str = '<pre>'.$str;
            }
            
            echo $str;
        }
        
        // Notify event to observers
        self::instance()->notifyEvent($str, PHPFrame_Subject::EVENT_TYPE_ERROR);
        exit;
    }
}
