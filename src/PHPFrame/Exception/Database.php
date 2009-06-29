<?php
/**
 * PHPFrame/Exception/Database.php
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
 * Database Exception Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Exception
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Exception_Database extends PHPFrame_Exception
{
    /**
     * SQLSTATE error code (a five characters alphanumeric identifier defined 
     * in the ANSI SQL standard)
     * 
     * @var string
     */
    private $_sqlstate=null;
    /**
     * Driver specific error code.
     * 
     * @var int
     */
    private $_driver_code=null;
    /**
     * Driver specific error message.
     * 
     * @var int
     */
    private $_driver_msg=null;
    
    /**
     * Constructor
     * 
     * @param string       $message   The error message.
     * @param int          $code      The error code.
     * @param PDOStatement $statement 
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(
        $message=null, 
        $code=PHPFrame_Exception::ERROR,
        PDOStatement $stmt=null
    ) {
        if ($stmt instanceof PDOStatement) {
             $error_info = $stmt->errorInfo();
             $this->_sqlstate = $error_info[0];
             $this->_driver_code = $error_info[1];
             $this->_driver_msg = $error_info[2];
        }
        
        $verbose = "SQLSTATE: ". $this->_sqlstate;
        $verbose .= "\nDriver code: ".$this->_driver_code;
        $verbose .= "\nDriver message".$this->_driver_msg;
        
        parent::__construct($message, $code, $verbose);
    }
}
