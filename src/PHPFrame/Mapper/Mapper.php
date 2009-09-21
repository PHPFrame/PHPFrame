<?php
/**
 * PHPFrame/Mapper/Mapper.php
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
 * This class is a facade class to simplify the interface of the whole Mapper 
 * subpackage.
 * 
 * This class should be extended to provide more specialised mappers for common 
 * persistent objects that require mapping. See the UsersMapper class in the user 
 * feature for an example. 
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @todo     XML storage needs to be replaced with SQLite3 as a save alternative to 
 *           a database server.
 */
class PHPFrame_Mapper
{
    const STORAGE_SQL = 0x00000001;
    const STORAGE_XML = 0x00000002;
    
    /**
     * Persistence Factory object used for the current mapper
     * 
     * @var PHPFrame_PersistenceFactory
     */
    private $_factory;
    
    /**
     * Constructor
     * 
     * @param string $target_class
     * @param string $table_name
     * @param int    $storage
     * @param bool   $try_alternative_storage
     * @param string $path
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(
        $target_class, 
        $table_name=null, 
        $storage=self::STORAGE_SQL, 
        $try_alternative_storage=true,
        $path=null
    ) {
        // Check if DB feature is enabled before allowing its use
        // We need this in order to implement fallback to XML storage
        $db_enable = (bool) PHPFrame::Config()->get("db.enable");
        
        if ($storage == self::STORAGE_SQL && !$db_enable) {
            if ($try_alternative_storage) {
                $storage = self::STORAGE_XML;
            } else {
                $msg = "SQL Persistance is not enabled and fallback to XML ";
                $msg .= "storage was explicitly disabled in constructor.";
                throw new LogicException($msg);
            }
        }
        
        switch ($storage) {
            case self::STORAGE_SQL :
                $class_name = "PHPFrame_SQLPersistenceFactory";
                $this->_factory = new $class_name($target_class, $table_name);
                break;
            
            case self::STORAGE_XML :
                $class_name = "PHPFrame_XMLPersistenceFactory";
                $this->_factory = new $class_name($target_class, $table_name, $path);
                break;
            
            default :
                $msg = "Storage mechanism not supported";
                throw new LogicException($msg);
        }
    }
    
    /**
     * Find a persistent object using an IdObject
     * 
     * @param PHPFrame_IdObject|int $id_obj
     * 
     * @access public
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    public function findOne($id_obj)
    {
        $obj = $this->_factory->getAssembler()->findOne($id_obj);
        
        if (
            PHPFrame::getRunLevel() > 1 
            && $obj instanceof PHPFrame_PersistentObject
            && !$obj->canRead(PHPFrame::Session()->getUser())
        ) {
            throw new PHPFrame_AccessDeniedException();
        }
        
        return $obj;
    }
    
    /**
     * Find a collection of persistent objects using an IdObject
     * 
     * @param PHPFrame_IdObject $id_obj
     * 
     * @access public
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function find(PHPFrame_IdObject $id_obj=null)
    {
        $collection = $this->_factory->getAssembler()->find($id_obj);
        
        foreach ($collection as $obj) {
            if (
                PHPFrame::getRunLevel() > 1 
                && !$obj->canRead(PHPFrame::Session()->getUser())
            ) {
                throw new PHPFrame_AccessDeniedException();
            }
        }
        
        return $collection;
    }
    
    /**
     * Persist persistent object
     * 
     * @param PHPFrame_PersistentObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function insert(PHPFrame_PersistentObject $obj)
    {
        if (
            PHPFrame::getRunLevel() > 1 
            && !$obj->canWrite(PHPFrame::Session()->getUser())
        ) {
            throw new PHPFrame_AccessDeniedException();
        }
        
        return $this->_factory->getAssembler()->insert($obj);
    }
    
    /**
     * Delete persistent object from persisted media (db or file)
     * 
     * @param PHPFrame_PersistentObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function delete(PHPFrame_PersistentObject $obj)
    {
        if (
            PHPFrame::getRunLevel() > 1 
            && !$obj->canWrite(PHPFrame::Session()->getUser())
        ) {
            throw new PHPFrame_AccessDeniedException();
        }
        
        return $this->_factory->getAssembler()->delete($obj);
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
        return $this->_factory->getIdObject();
    }
}
