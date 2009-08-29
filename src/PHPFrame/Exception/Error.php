<?php
/**
 * PHPFrame/Exception/Error.php
 * 
 * PHP version 5
 * 
 * @category PHPFrame
 * @package   Exception
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Error Exception Class
 * 
 * This class handles exceptions produced by PHP errors.
 * 
 * Note that PHP's fatal errors are not converted into exceptions.
 * 
 * @category PHPFrame
 * @package   Exception
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Exception_Error extends PHPFrame_Exception
{
    /**
     * The PHP Error Context
     *
     * The fifth parameter is optional, errcontext, which is an array that points to 
     * the active symbol table at the point the error occurred. In other words, 
     * errcontext  will contain an array of every variable that existed in the scope 
     * the error was triggered in. User error handler must not modify error context.
     * 
     * @var array
     */
    private $_context;

    /**
     * Constructor
     * 
     * @param string $message The error message.
     * @param int    $code    The error code.
     * @param string $file    A string with the path to the file where the error occurred.
     * @param int    $line    The line number where the error occurred.
     * @param array  $context The context array.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($message, $code, $file, $line, $context=null) 
    {
        $this->_context = $context;
        
        switch ($code) {
            case E_ERROR :
            case E_PARSE :
            case E_USER_ERROR :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
            case E_RECOVERABLE_ERROR :
                $code = PHPFrame_Exception::ERROR;
                break;
                
            case E_WARNING :
            case E_USER_WARNING :
            case E_CORE_WARNING :
            case E_COMPILE_WARNING :
                $code = PHPFrame_Exception::WARNING;
                break;
                
            case E_NOTICE :
            case E_USER_NOTICE :
                $code = PHPFrame_Exception::NOTICE;
                break;
                
            case E_STRICT :
            case E_DEPRECATED :
            case E_USER_DEPRECATED :
                $code = PHPFrame_Exception::STRICT;
                break;
        }
        
        // Invoke parent
        parent::__construct($message, $code);
        
        $this->file = $file;
        $this->line = $line;
    }
}
