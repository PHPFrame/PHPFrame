<?php
/**
 * PHPFrame/Mapper/SQLPersistenceFactory.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * SQL Persistence Factory Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_SQLPersistenceFactory extends PHPFrame_PersistenceFactory
{
    private $_db;
    
    /**
     * Constructor
     * 
     * @param string            $target_class
     * @param string            $table_name
     * @param PHPFrame_Database $db           [Optional]
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(
        $target_class, 
        $table_name, 
        PHPFrame_Database $db=null
    )
    {
        parent::__construct($target_class, $table_name);
        
        if ($db instanceof PHPFrame_Database) {
            $this->_db = $db;
        } else {
            $this->_db = PHPFrame::DB();
        }
    }
    
    /**
     * Get object assembler
     * 
     * @access public
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
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function getIdObject()
    {
        $options = array("select"=>"*", "from"=>$this->getTableName());
        
        return new PHPFrame_SQLIdObject($options);
    }
    
    public function getDB()
    {
        return $this->_db;
    }
}
