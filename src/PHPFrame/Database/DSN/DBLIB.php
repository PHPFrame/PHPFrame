<?php
/**
 * PHPFrame/Database/DSN/DBLIB.php
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
 * Concrete Oracle DSN (Database Source Name) class
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
class PHPFrame_Database_DSN_DBLIB extends PHPFrame_Database_DSN
{
    /**
     * Constructor
     * 
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        // ...
        $exception_msg = "Oops... This database DSN has not been implemented yet.";
        throw new PHPFrame_Exception($exception_msg);
    }
    
    /**
     * Convert object to string
     * 
     * @return string
     * @since 1.0
     */
    public function toString()
    {
        // ...
    }
    
    /**
     * Convert object to array
     * 
     * @return array
     * @since 1.0
     */
    public function toArray() 
    {
        // ...
    }
}
