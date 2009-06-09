<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    exception
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Error Exception Class
 * 
 * This class handles exceptions produced by PHP errors.
 * 
 * Note that PHP's fatal errors are not converted into exceptions.
 * 
 * @package        PHPFrame
 * @subpackage     exception
 * @since         1.0
 */
class PHPFrame_Exception_Error extends PHPFrame_Exception 
{
    /**
     * The PHP Error Context
     *
     * The fifth parameter is optional, errcontext, which is an array that points to the active symbol 
     * table at the point the error occurred. In other words, errcontext  will contain an array of 
     * every variable that existed in the scope the error was triggered in. User error handler must 
     * not modify error context.
     * 
     * @access    private
     * @var        array
     */
    private $_context;

    /**
     * Constructor
     * 
     * @access    public
     * @param    string    $message    The error message.
     * @param    int        $code        The error code.
     * @param    string    $file        A string with the path to the file where the error occurred.
     * @param    int        $line        The line number where the error occurred.
     * @param    array    $context    The context array.
     * @return    void
     * @since    1.0
     */
    public function __construct($message, $code, $file, $line, $context=null) 
    {
        parent::__construct($message, $code);
        
        $this->file = $file;
        $this->line = $line;
        
        $this->_context = $context;
    }
}
