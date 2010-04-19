<?php
/**
 * PHPFrame/Exception/DatabaseException.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Exception
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Database Exception Class
 *
 * @category PHPFrame
 * @package  Exception
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_DatabaseException extends RuntimeException
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
     * @param string       $msg  The error message.
     * @param PDOStatement $stmt Reference to PDO statement.
     *
     * @return void
     * @since  1.0
     */
    public function __construct($msg=null, PDOStatement $stmt=null)
    {
        if ($stmt instanceof PDOStatement) {
             $error_info         = $stmt->errorInfo();
             $this->_sqlstate    = $error_info[0];
             $this->_driver_code = $error_info[1];
             $this->_driver_msg  = $error_info[2];
        }

        $msg .= "\nSQLSTATE: ". $this->_sqlstate;
        $msg .= "\nDriver code: ".$this->_driver_code;
        $msg .= "\nDriver message: ".$this->_driver_msg;

        parent::__construct($msg);
    }
}
