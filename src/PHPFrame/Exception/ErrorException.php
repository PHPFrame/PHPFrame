<?php
/**
 * PHPFrame/Exception/ErrorException.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Exception
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Error Exception Class
 * 
 * This class handles exceptions produced by PHP errors.
 * 
 * Note that PHP's fatal errors are not converted into exceptions.
 * 
 * @category PHPFrame
 * @package  Exception
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_ErrorException extends Exception
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
        $this->file     = $file;
        $this->line     = $line;
        
        // Invoke parent
        parent::__construct($message, $code);
    }
}
