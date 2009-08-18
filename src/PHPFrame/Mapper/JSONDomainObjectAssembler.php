<?php
/**
 * PHPFrame/Mapper/JSONDomainObjectAssembler.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: JSONDomainObjectAssembler.php 460 2009-08-18 20:16:02Z luis.montero@e-noise.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * JSON Domain Object Assembler Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Mapper_JSONDomainObjectAssembler extends PHPFrame_Mapper_DomainObjectAssembler
{
    private $_path_info=null;
    private $_file_info=null;
    
    /**
     * Constructor
     * 
     * @param PHPFrame_Mapper_PersistenceFactory $factory
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Mapper_PersistenceFactory $factory, $path=null)
    {
        parent::__construct($factory);
        
        if (!defined("PHPFRAME_VAR_DIR") && is_null($path)) {
            $msg = "No path has been defined to store XML domain objects. ";
            $msg .= "If you are trying to use the Mapper package outside of an ";
            $msg .= "MVC app you can manually set the PHPFRAME_VAR_DIR constant ";
            $msg .= "before you instantiate the mapper objects.";
            throw new PHPFrame_Exception($msg);
            
        }
        
        // If no path is specified we use default location
        if (is_null($path)) {
            $path = PHPFRAME_VAR_DIR.DS."domain.objects";
        }
        
        // Make sure the directory is writable
        PHPFrame_Utils_Filesystem::ensureWritableDir($path);
        
        // Create FileInfo object for dir path
        $this->_path_info = new PHPFrame_FS_FileInfo($path);
        
        // Build full path to XML file
        $file_name = $this->_path_info->getRealPath();
        $file_name .= DS.$this->factory->getTableName().".xml";
        
        // Create FileInfo object for XML file
        // Create XML file if it doesnt exist
        if (!is_file($file_name)) {
            $file_obj = new PHPFrame_FS_FileObject($file_name, "w");
            $this->_file_info = $file_obj->getFileInfo();
        } else {
            $this->_file_info = new PHPFrame_FS_FileInfo($file_name);
        }
    }
    
    /**
     * Find a domain object using an IdObject
     * 
     * @param PHPFrame_Mapper_IdObject $id_obj
     * 
     * @access public
     * @return PHPFrame_Mapper_DomainObject
     * @since  1.0
     */
    public function findOne($id_obj)
    {
        if (is_int($id_obj)) {
            $id = $id_obj;
            
            // Get table name
            $table_name = $this->_factory->getTableName();
            
            // Create new IdObject
            $id_obj = new PHPFrame_Mapper_IdObject(array("select"=>"*", "from"=>$table_name));
            $id_obj->where("id", "=", ":id")->params(":id", $id);
        }
        
        if (!$id_obj instanceof PHPFrame_Mapper_IdObject) {
            $msg = "Wrong argument type. ";
            $msg .= get_class($this)."::findOne() expected only argument to be of type ";
            $msg .= "PHPFrame_Mapper_IdObject or integer.";
            throw new PHPFrame_Exception($msg);
        }
        
        $collection = $this->find($id_obj);
        
        return $collection->getElement(0);
    }
    
    /**
     * Find a collection of domain objects using an IdObject
     * 
     * @param PHPFrame_Mapper_IdObject|int $id_obj
     * 
     * @access public
     * @return PHPFrame_Mapper_Collection
     * @since  1.0
     */
    public function find(PHPFrame_Mapper_IdObject $id_obj=null)
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
        
        //var_dump($raw);
        
        // Create collectioj object
        $collection = $this->factory->getCollection($raw);
        
        return $collection;
    }
    
    /**
     * Persist domain object
     * 
     * @param PHPFrame_Mapper_DomainObject $obj
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function insert(PHPFrame_Mapper_DomainObject $obj)
    {
        // Get current collection
        $collection = $this->find();
        
        // Update modified time
        $obj->setModified(date("Y-m-d H:i:s"));
        
        // Prepare new elements (insert)
        if ($obj->getId() <= 0) {
            $obj->setId($this->_getNewId());
            $obj->setCreated(date("Y-m-d H:i:s"));
            
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
    
    private function _serializeCollection(PHPFrame_Mapper_Collection $collection)
    {
        $options = array(
            "indent"    => "    ",
            "rootName"=> "collection",
            "defaultTagName"=> $this->factory->getTargetClass()
        );
        
        // Flatten collectio object to array
        $array = array();
        foreach ($collection as $item) {
            $array[] = $item->toArray();
        }
        
        $serialiser = new XML_Serializer($options);
        $serialiser->serialize($array);
        $serialised = $serialiser->getSerializedData();
        
        return $serialised;
    }
    
    private function _getNewId()
    {
        $newid = 1;
        
        $collection = $this->find();
        foreach ($collection as $item) {
            if ($item->getId() > $newid) {
                $newid = $item->getId();
            }
        }
        
        return ($newid+1);
    }
}
