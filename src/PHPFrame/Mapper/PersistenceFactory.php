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
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Abstract Persistence Factory Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
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
     * @param string $target_class The persistent object class this factory will
     *                             work with.
     * @param string $table_name   [Optional] The table name where we will be 
     *                             mapping the persistent objects. If omitted 
     *                             the table name will be assumed to be the same 
     *                             as the target class. 
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
     * @param string $target_class The persistent object class this factory will
     *                             work with.
     * @param string $table_name   The table name where we will be mapping the 
     *                             persistent objects.
     * @param int    $storage      [Optional] Storage mechanism. If omitted SQL 
     *                             storage will be used. For supported storage 
     *                             mechanisms see class constants.
     *                       
     * 
     * @return PHPFrame_PersistenceFactory
     * @since  1.0
     */
    public static function getFactory(
        $target_class, 
        $table_name, 
        $storage=self::STORAGE_SQL
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
     * @param array $raw        [Optional] Array containig the raw collection 
     *                          data.
     * @param int   $total      [Optional] The total number of records in the 
     *                          superset.
     * @param int   $limit      [Optional] The number of records the current 
     *                          subset is lmited to. Default value is '-1', 
     *                          which means there is no limit, so we will get 
     *                          all the records.
     * @param int   $limitstart [Optional] The entry number from which to start 
     *                          the subset. If ommited default value '0' will 
     *                          be used, meaning that we start from the first 
     *                          page of results.
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
