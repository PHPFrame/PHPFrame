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
     * Should exceptions be displayed?
     * 
     * @var bool
     */
    private $_display_exceptions = true;
    
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
        
        // Notify event to observers
        self::instance()->notifyEvent($str, PHPFrame_Subject::EVENT_TYPE_ERROR);
        
        // Display the exception details if applicable
        if (self::instance()->_display_exceptions) {
        	$response = new PHPFrame_Response();
            $response->setDocument(new PHPFrame_PlainDocument());
            
            $status_code = $exception->getCode();
            if (!empty($status_code) && $status_code > 0) {
                $response->setStatusCode($status_code);
            } else {
                $response->setStatusCode(500);
            }
            
            $response->getDocument()->setBody($str, false);
            $response->send();
        }
        
        exit;
    }
    
    /**
     * Set boolean to indicate whether or not exceptions should be displayed.
     * 
     * @param $bool
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public static function setDisplayExceptions($bool)
    {
    	self::instance()->_display_exceptions = (bool) $bool;
    }
}
