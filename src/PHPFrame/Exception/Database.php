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
     * MySQL error message
     * 
     * @access    private
     * @var        string
     */
    private $_mysql_error=null;
    /**
     * MySQL error number
     * 
     * @access    private
     * @var        int
     */
    private $_mysql_errno=null;
    
    /**
     * Constructor
     * 
     * @access    public
     * @param    string    $message    The error message.
     * @param    int        $code        The error code.
     * @param    string    $query
     * @return    void
     * @since    1.0
     */
    public function __construct($message=null, $query="", $code=self::E_USER_ERROR) 
    {
        $this->_mysql_error = mysql_error();
        $this->_mysql_errno = mysql_errno();
        
        $verbose = "MySQL Error Number: ".$this->_mysql_errno."\n";
        $verbose .= "MySQL Server said: ".$this->_mysql_error;
        
        parent::__construct($message, $code, $verbose);
    }
}
