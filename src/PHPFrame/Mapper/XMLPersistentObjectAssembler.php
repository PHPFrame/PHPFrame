<?php
/**
 * PHPFrame/Mapper/XMLPersistentObjectAssembler.php
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
 * XML Domain Object Assembler Class
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_XMLPersistentObjectAssembler 
    extends PHPFrame_PersistentObjectAssembler
{
    private $_path_info = null;
    private $_file_info = null;
    
    /**
     * Constructor
     * 
     * @param PHPFrame_PersistenceFactory $factory
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_PersistenceFactory $factory, $path=null)
    {
        parent::__construct($factory);
        
        if (!defined("PHPFRAME_VAR_DIR") && is_null($path)) {
            $msg = "No path has been defined to store XML persistent objects.";
            $msg .= " If you are trying to use the Mapper package outside of";
            $msg .= " an MVC app you can manually set the PHPFRAME_VAR_DIR";
            $msg .= " constant before you instantiate the mapper objects.";
            throw new RuntimeException($msg);
            
        }
        
        // If no path is specified we use default location
        if (is_null($path)) {
            $path = PHPFRAME_VAR_DIR.DS."domain.objects";
        }
        
        // Make sure the directory is writable
        PHPFrame_Filesystem::ensureWritableDir($path);
        
        // Create FileInfo object for dir path
        $this->_path_info = new PHPFrame_FileInfo($path);
        
        // Build full path to XML file
        $file_name = $this->_path_info->getRealPath();
        $file_name .= DS.$this->factory->getTableName().".xml";
        
        // Create FileInfo object for XML file
        // Create XML file if it doesnt exist
        if (!is_file($file_name)) {
            $file_obj = new PHPFrame_FileObject($file_name, "w");
            $this->_file_info = $file_obj->getFileInfo();
        } else {
            $this->_file_info = new PHPFrame_FileInfo($file_name);
        }
    }
    
    /**
     * Find a persistent object using an IdObject
     * 
     * @param PHPFrame_IdObject $id_obj
     * 
     * @access public
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    public function findOne($id_obj)
    {
        if (is_int($id_obj)) {
            $id = $id_obj;
            
            // Get table name
            $table_name = $this->_factory->getTableName();
            
            // Create new IdObject
            $options = array("select"=>"*", "from"=>$table_name);
            $id_obj = new PHPFrame_IdObject($options);
            $id_obj->where("id", "=", ":id")->params(":id", $id);
        }
        
        if (!$id_obj instanceof PHPFrame_IdObject) {
            $msg = "Wrong argument type. ";
            $msg .= get_class($this)."::findOne() expected only argument to be";
            $msg .= " of type PHPFrame_IdObject or integer.";
            throw new RuntimeException($msg);
        }
        
        $collection = $this->find($id_obj);
        
        return $collection->getElement(0);
    }
    
    /**
     * Find a collection of persistent objects using an IdObject
     * 
     * @param PHPFrame_IdObject|int $id_obj
     * 
     * @access public
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function find(PHPFrame_IdObject $id_obj=null)
    {
//        if (is_null($id_obj)) {
//            $id_obj = $this->factory->getIdObject();
//        }
        
        // Get raw data as array from XML
        $serialiser = new XML_Unserializer();
        
        $raw = array();
        
        if ($serialiser->unserialize($this->_file_info->getRealPath(), true)) {
            $raw_tmp = $serialiser->getUnserializedData();
            if (!$raw_tmp instanceof PEAR_Error) {
                
                $raw = $raw_tmp[$this->factory->getTargetClass()];
                
            }
        }
        
        // Create collection object
        return $this->factory->getCollection($raw);
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
        // Get current collection
        $collection = $this->find();
        
        // Update modified time
        $obj->setMTime(time());
        
        // Prepare new elements (insert)
        if ($obj->getId() <= 0) {
            $obj->setId($this->_getNewId());
            $obj->setCTime(time());
            
            // Add new element to collection
            $collection->addElement($obj);
        
        // Prepare existing elements (update)
        } else {
            foreach ($collection as $item) {
                if ($item->getId() == $obj->getId()) {
                    $item = $obj->toArray();
                }
            }
        }
        
        // Open the file in "write" mode
        $file_obj = $this->_file_info->openFile("w");
        $file_obj->fwrite($this->_serializeCollection($collection));
        
        $obj->markClean();
    }
    
    /**
     * Delete persistent object from the database
     * 
     * @param PHPFrame_PersistentObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function delete(PHPFrame_PersistentObject $obj)
    {
        throw new RuntimeException("Method not implemented...");
    }
    
    /**
     * Serialise collection as an XML string
     * 
     * @param PHPFrame_PersistentObjectCollection $collection
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _serializeCollection(
        PHPFrame_PersistentObjectCollection $collection
    )
    {
        $options = array(
            "indent"    => "    ",
            "rootName"=> "collection",
            "defaultTagName"=> $this->factory->getTargetClass()
        );
        
        // Flatten collectio object to array
        $array = array();
        
        foreach ($collection as $item) {
            $array[] = iterator_to_array($item);
        }
        
        $serialiser = new XML_Serializer($options);
        $serialiser->serialize($array);
        $serialised = $serialiser->getSerializedData();
        
        return $serialised;
    }
    
    /**
     * Get new id based on highest in current collection
     * 
     * @access private
     * @return int
     * @since  1.0
     */
    private function _getNewId()
    {
        $newid = 0;
        
        $collection = $this->find();
        foreach ($collection as $item) {
            if ($item->getId() > $newid) {
                $newid = $item->getId();
            }
        }
        
        return ($newid+1);
    }
}
