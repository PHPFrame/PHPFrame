<?php
/**
 * PHPFrame/Database/DSN.php
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
 * Abstract DSN (Database Source Name) class
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
 * @abstract
 */
abstract class PHPFrame_Database_DSN
{
    /**
     * A string identifying the database driver
     * 
     * @var string
     */
    protected $db_driver="";
    
    /**
     * Constructor
     * 
     * @param string $db.driver A string identifying the database driver. Possible 
     *                          values are: mysql, pgsql, OCI, ODBC, ...
     *                          
     * @since 1.0    
     */
    public function __construct($db_driver) 
    {
        $this->db_driver = $db_driver;
    }
    
    /**
     * Magic method used when DSN object is used as a string
     * 
     * @return string
     * @since 1.0
     */
    public function __toString() 
    {
        return $this->toString();
    }
    
    /**
     * Convert object to string
     * 
     * @return string
     * @since 1.0
     */
    abstract public function toString();
    
    /**
     * Convert object to array
     * 
     * @return array
     * @since 1.0
     */
    abstract public function toArray();
}
