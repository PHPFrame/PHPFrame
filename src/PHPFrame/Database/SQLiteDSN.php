<?php
/**
 * PHPFrame/Database/SQLiteDSN.php
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
 * Concrete SQLite (Database Source Name) class
 * 
 * @category PHPFrame
 * @package  Database
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_SQLiteDSN extends PHPFrame_DSN
{
    /**
     * Constructor
     * 
     * @param string $filename Absolute path to the sqlite3 db file
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($filename) 
    {
        $this->array["filename"] = trim((string) $filename);
        
        parent::__construct("sqlite");
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
        $str = $this->array["db_driver"].":".$this->array["filename"];
        
        return $str;
    }
}
