<?php
/**
 * PHPFrame/Mapper/PersistenceFactory.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * Abstract Persistence Factory Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_PersistenceFactory
{
    /**
     * Target class
     * 
     * @var string
     */
    private $_target_class = null;
    /**
     * Target class
     * 
     * @var string
     */
    private $_table_name = null;
    
    /**
     * Constructor
     * 
     * @param string $target_class
     * @param string $table_name
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($target_class, $table_name=null)
    {
        $this->_target_class = trim((string) $target_class);
        
        if (!is_null($table_name)) {
            $this->_table_name = trim((string) $table_name);
        } else {
            $this->_table_name = $this->_target_class;
        }
        
    }
    
    /**
     * Get concrete persistence factory
     * 
     * @param string $target_class
     * @param string $table_name
     * @param int    $storage
     * @param bool   $try_alternative_storage
     * 
     * @return PHPFrame_PersistenceFactory
     * @since  1.0
     */
    public static function getFactory(
        $target_class, 
        $table_name, 
        $storage=self::STORAGE_SQL, 
        $try_alternative_storage=true
    ) {
        switch ($storage) {
        case self::STORAGE_SQL :
            $class_name = "PHPFrame_SQLPersistenceFactory";
            break;
            
        case self::STORAGE_XML :
            $class_name = "PHPFrame_XMLPersistenceFactory";
            break;
        
        default :
            throw new RuntimeException("Storage mechanism not supported");
        }
        
        $factory = new $class_name($target_class, $table_name);
        
        return $factory;
    }
    
    /**
     * Get PersistentObjectFactory
     * 
     * @return PHPFrame_PersistentObjectFactory
     * @since  1.0
     */
    public function getPersistentObjectFactory()
    {
        return new PHPFrame_PersistentObjectFactory($this->_target_class);
    }
    
    /**
     * Get Collection
     * 
     * @param array $raw
     * @param int   $total
     * @param int   $limit
     * @param int   $limitstart
     * 
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function getCollection(
        array $raw=null, 
        $total=null, 
        $limit=-1, 
        $limitstart=0
    ) {
        return new PHPFrame_PersistentObjectCollection(
            $raw, 
            $this->getPersistentObjectFactory(),
            $total, 
            $limit, 
            $limitstart
        );
    }
    
    /**
     * Get target class
     * 
     * @return string
     * @since  1.0
     */
    public function getTargetClass()
    {
        return $this->_target_class;
    }
    
    /**
     * Get table name
     * 
     * @return string
     * @since  1.0
     */
    public function getTableName()
    {
        return $this->_table_name;
    }
    
    /**
     * Get object assembler
     * 
     * @return PHPFrame_PersistentObjectAssembler
     * @since  1.0
     */
    abstract public function getAssembler();
    
    /**
     * Create a new IdObject to work with the target class
     * 
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    abstract public function getIdObject();
}
