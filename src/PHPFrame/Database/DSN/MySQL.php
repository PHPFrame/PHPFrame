<?php
/**
 * PHPFrame/Database/DSN/MySQL.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */

/**
 * Concrete MySQL DSN (Database Source Name) class
 * 
 * This class deals with the connection(s) to the database(s).
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Database_DSN_MySQL extends PHPFrame_Database_DSN
{
    /**
     * The MySQL server host name
     * 
     * @var string
     */
    private $_db_host=null;
    /**
     * The MySQL database name
     * 
     * @var string
     */
    private $_db_name=null;
    /**
     * Unix socket
     * 
     * @var string
     */
    private $_unix_socket=null;
    
    /**
     * Constructor
     * 
     * @param string $db_host     The MySQL server host name
     * @param string $db_name     The MySQL database name
     * @param string $unix_socket Path to unix socket
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($db_host, $db_name, $unix_socket=null) 
    {
        $this->_db_host = $db_host;
        $this->_db_name = $db_name;
        
        if (!is_null($unix_socket)) {
            $this->_unix_socket = (string) $unix_socket;
        } else {
            $this->_unix_socket = ini_get('mysql.default_socket');
        }
        
        parent::__construct("mysql");
    }
    
    /**
     * Convert object to string
     * 
     * @return string
     * @since 1.0
     */
    public function toString()
    {
        $str = $this->db_driver.":";
        $str .= "host=".$this->_db_host.";";
        $str .= "dbname=".$this->_db_name;
        
        if (!is_null($this->_unix_socket)) {
            $str .= ";unix_socket=".$this->_unix_socket;
        }
        
        return $str;
    }
    
    /**
     * Convert object to array
     * 
     * @return array
     * @since 1.0
     */
    public function toArray() 
    {
        $array = array();
        $array['db.driver'] = $this->db.driver;
        $array['db_host'] = $this->_db_host;
        $array['db_name'] = $this->_db_name;
        
        return $array;
    }
}
