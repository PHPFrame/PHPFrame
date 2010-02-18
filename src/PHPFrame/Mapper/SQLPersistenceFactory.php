<?php
/**
 * PHPFrame/Mapper/SQLPersistenceFactory.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * SQL Persistence Factory Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_SQLPersistenceFactory extends PHPFrame_PersistenceFactory
{
    private $_db;
    
    /**
     * Constructor
     * 
     * @param string            $target_class The target class for this factory.
     * @param string            $table_name   The table name the target class is 
     *                                        mapped to.
     * @param PHPFrame_Database $db           Reference to the databse object.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(
        $target_class, 
        $table_name, 
        PHPFrame_Database $db
    ) {
        parent::__construct($target_class, $table_name);
        
        $this->_db = $db;
    }
    
    /**
     * Get object assembler
     * 
     * @return PHPFrame_PersistentObjectAssembler
     * @since  1.0
     */
    public function getAssembler()
    {
        return new PHPFrame_SQLPersistentObjectAssembler($this);
    }
    
    /**
     * Create a new IdObject to work with the target class
     * 
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function getIdObject()
    {
        $options = array("select"=>"*", "from"=>$this->getTableName());
        
        return new PHPFrame_SQLIdObject($options);
    }
    
    /**
     * Get reference to database object.
     * 
     * @return PHPFrame_Database
     * @since  1.0
     */
    public function getDB()
    {
        return $this->_db;
    }
}
