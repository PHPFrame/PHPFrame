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
    private $_db_host=null;
    private $_db_name=null;
    
    public function __construct($db_host, $db_name) 
    {
        $this->_db_host = $db_host;
        $this->_db_name = $db_name;
        
        parent::__construct("mysql");
    }
    
    public function asString()
    {
        $str = $this->db_driver.":";
        $str .= "host=".$this->_db_host.";";
        $str .= "dbname=".$this->_db_name;
        return $str;
    }
    
    public function asArray() {}
}
