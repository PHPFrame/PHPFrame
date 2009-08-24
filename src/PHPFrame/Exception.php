<?php
/**
 * PHPFrame/Exception.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Exception
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Exception Class
 * 
 * Extends PHP's built in Exception.
 * 
 * <code>
 *  class Exception {
 *  protected $message = 'Unknown exception';   // exception message
 *  protected $code = 0;                        // user defined exception code
 *  protected $file;                            // source filename of exception
 *  protected $line;                            // source line of exception
 *  
 *  function __construct($message = null, $code = 0);
 *  
 *  final function getMessage();                // message of exception 
 *  final function getCode();                   // code of exception
 *  final function getFile();                   // source filename
 *  final function getLine();                   // source line
 *  final function getTrace();                  // an array of the backtrace()
 *  final function getTraceAsString();          // formatted string of trace
 *  
 *  // Overrideable
 *  function __toString();                       // formatted string for display
 *  }
 *  </code>
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Exception
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Exception extends Exception
{
    // PHPFrame exception codes
    const ERROR   = 0x00000001;
    const WARNING = 0x00000002;
    const NOTICE  = 0x00000003;
    const STRICT  = 0x00000004;
    
    /**
     * A string containing more information about the exception
     * 
     * @var string
     */
    protected $_verbose=null;
    
    /**
     * Constructor
     * 
     * @param string $message The exception message
     * @param int    $code    The exception code. See class constants
     * @param string $verbose A string with more info about the exception
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($message, $code=self::ERROR, $verbose='') 
    {
        $this->_verbose = $verbose;
        
        // Construct parent class to build Exception 
        parent::__construct($message, $code);
        
        // Log the exception to file if needed
        //if ($code < PHPFrame::Config()->get("LOG_LEVEL")) {
            // Cast exception object to string
            //$exception = (string) $this;
            // Write log
            //PHPFrame_Debug_Logger::write($exception);
        //}
    }
    
    /**
     * Magig method invoked when object is used as string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString() 
    {
        $str = parent::__toString();
        $str .= "\n\nVerbose:\n";
        $str .= $this->_verbose;
        
        return $str;
    }
    
    /**
     * Get verbose
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getVerbose() 
    {
        return $this->_verbose;
    }
}
