<?php
/**
 * PHPFrame/Database/DSN.php
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
 * Abstract DSN (Database Source Name) class
 * 
 * @category PHPFrame
 * @package  Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @abstract
 */
abstract class PHPFrame_DSN extends ArrayObject
{
    /**
     * Internal array holding data
     * 
     * @var array
     */
    protected $array=array();
    
    /**
     * Constructor
     * 
     * @param string $db_driver A string identifying the database driver. Possible 
     *                          values are: mysql, pgsql, OCI, ODBC, ...
     * 
     * @access public
     * @return void
     * @since  1.0    
     */
    public function __construct($db_driver) 
    {
        $this->array["db_driver"] = trim((string) $db_driver);
        
        parent::__construct($this->array);
    }
    
    /**
     * Convert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    abstract public function __toString();
}
