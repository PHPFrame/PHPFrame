<?php
/**
 * PHPFrame/Mapper/XMLPersistentObjectAssembler.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Mapper
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * XML Domain Object Assembler Class
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_XMLPersistentObjectAssembler
    extends PHPFrame_PersistentObjectAssembler
{
    private $_path_info;
    private $_file_name;

    /**
     * Constructor
     *
     * @param PHPFrame_PersistenceFactory $factory Instance of persistence
     *                                             factory to be used with the
     *                                             assembler.
     * @param string                      $path    Path to directory where to
     *                                             store the XML file.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_PersistenceFactory $factory, $path)
    {
        parent::__construct($factory);

        // Create FileInfo object for dir path
        $this->_path_info = new SplFileInfo($path);

        // Build full path to XML file
        $this->_file_name  = $this->_path_info->getRealPath();
        $this->_file_name .= DS.$this->factory->getTableName().".xml";
    }

    /**
     * Find a persistent object using an IdObject
     *
     * @param int|PHPFrame_IdObject $id_or_id_obj Either a numeric id or an
     *                                            instance of IdObject.
     *
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    public function findOne($id_or_id_obj)
    {
        if (is_int($id_or_id_obj)) {
            $id = $id_or_id_obj;

        } elseif ($id_or_id_obj instanceof PHPFrame_IdObject) {
            $msg = "XMLIdObject not implemented!!!.";
            throw new RuntimeException($msg);

        } else {
            $msg = "Wrong argument type. ";
            $msg .= get_class($this)."::findOne() expected only argument to be";
            $msg .= " of type PHPFrame_IdObject or integer.";
            throw new InvalidArgumentException($msg);
        }

        foreach ($this->find() as $obj) {
            if ($obj->id() == $id) {
                return $obj;
            }
        }
    }

    /**
     * Find a collection of persistent objects using an IdObject
     *
     * @param PHPFrame_IdObject $id_obj Instance of {@link PHPFrame_IdObject}.
     *
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function find(PHPFrame_IdObject $id_obj=null)
    {
        $raw_tmp = array();

        if (is_file($this->_file_name)) {
            $file_contents = file_get_contents($this->_file_name);
            if (!empty($file_contents)) {
                $array = PHPFrame_XMLSerialiser::unserialise($file_contents);
                if (is_array($array)) {
                    $raw_tmp = $array;
                }
            }
        }

        $raw = array();

        if (array_key_exists($this->factory->getTargetClass(), $raw_tmp)) {
            $raw = $raw_tmp[$this->factory->getTargetClass()];
        } else {
            $raw = $raw_tmp;
        }

        $raw_array_obj = new PHPFrame_Array($raw);
        if ($raw_array_obj->isAssoc()) {
            $raw = array($raw);
        }

        // Create collection object
        return $this->factory->getCollection($raw);
    }

    /**
     * Persist persistent object
     *
     * @param PHPFrame_PersistentObject $obj The persistent object we want to
     *                                       store with the mapper.
     *
     * @return void
     * @since  1.0
     */
    public function insert(PHPFrame_PersistentObject $obj)
    {
        // Get current collection
        $collection = $this->find();

        // Update modified time
        $obj->mtime(time());

        // Prepare new elements (insert)
        if ($obj->id() <= 0) {
            $obj->id($this->_getNewId());
            $obj->ctime(time());

            // Add new element to collection
            $collection->addElement($obj);

        } else {
            // Prepare existing elements (update)
            foreach ($collection as $item) {
                if ($item->id() == $obj->id()) {
                    $collection->removeElement($item);
                    $collection->addElement($obj);
                }
            }
        }

        // Open the file in "write" mode
        $file_obj = $this->_getFileInfo()->openFile("w");
        $file_obj->fwrite($this->_serializeCollection($collection));
    }

    /**
     * Delete persistent object from the database
     *
     * @param int|PHPFrame_PersistentObject $id_or_obj Either a numeric id or
     *                                                 an instance of the
     *                                                 persistence object.
     *
     * @return void
     * @since  1.0
     */
    public function delete($id_or_obj)
    {
        if (!$id_or_obj instanceof PHPFrame_PersistentObject) {
            $obj = $this->findOne((int) $id_or_obj);
        } else {
            $obj = $id_or_obj;
        }

        // Get current collection
        $collection = $this->find();
        $collection->removeElement($obj);

        // Open the file in "write" mode
        $file_obj = $this->_getFileInfo()->openFile("w");
        $file_obj->fwrite($this->_serializeCollection($collection));

        $obj->markClean();
    }

    /**
     * Serialise collection as an XML string
     *
     * @param PHPFrame_PersistentObjectCollection $collection Instance of
     *                                                        collection object
     *                                                        to serialise.
     *
     * @return string
     * @since  1.0
     */
    private function _serializeCollection(
        PHPFrame_PersistentObjectCollection $collection
    ) {
        // Flatten collection object to array
        $array = array();
        foreach ($collection as $item) {
            $array[get_class($item)][] = iterator_to_array($item);
        }

        return PHPFrame_XMLSerialiser::serialise($array, "collection");
    }

    /**
     * Get new id based on highest in current collection
     *
     * @return int
     * @since  1.0
     */
    private function _getNewId()
    {
        $newid = 0;

        $collection = $this->find();

        foreach ($collection as $item) {
            if ($item->id() > $newid) {
                $newid = $item->id();
            }
        }

        return ($newid+1);
    }

    /**
     * Get SplFileInfo object for XML file.
     *
     * @return SplFileInfo
     * @since  1.0
     */
    private function _getFileInfo()
    {
        // Create FileInfo object for XML file
        // Create XML file if it doesnt exist
        if (!is_file($this->_file_name)) {
            $file_obj = new SplFileObject($this->_file_name, "w");
            return $file_obj->getFileInfo();
        } else {
            return new SplFileInfo($this->_file_name);
        }
    }
}
