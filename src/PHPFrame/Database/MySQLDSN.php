<?php
/**
 * PHPFrame/Database/MySQLDSN.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Database
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since     1.0
 */

/**
 * Concrete MySQL DSN (Database Source Name) class
 * 
 * @category PHPFrame
 * @package  Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_MySQLDSN extends PHPFrame_DSN
{
    /**
     * Constructor
     * 
     * @param string $db_host     The MySQL server host name
     * @param string $db_name     The MySQL database name
     * @param string $unix_socket Path to unix socket
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($db_host, $db_name, $unix_socket=null) 
    {
        $this->array["db_host"] = $db_host;
        $this->array["db_name"] = $db_name;
        
        if (!is_null($unix_socket)) {
            $this->array["unix_socket"] = (string) $unix_socket;
        } else {
            $this->array["unix_socket"] = ini_get('mysql.default_socket');
        }
        
        parent::__construct("mysql");
    }
    
    /**
     * Convert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = $this->array["db_driver"].":";
        $str .= "host=".$this->array["db_host"].";";
        $str .= "dbname=".$this->array["db_name"];
        
        if (!isset($this->array["unix_socket"])) {
            $str .= ";unix_socket=".$this->array["unix_socket"];
        }
        
        return $str;
    }
}
