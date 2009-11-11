<?php
/**
 * PHPFrame/FileSystem/FileInfo.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   FileSystem
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * File Info Class
 * 
 * @category PHPFrame
 * @package  FileSystem
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      SplFileInfo, PHPFrame_FileObject
 * @since    1.0
 */
class PHPFrame_FileInfo extends SplFileInfo 
    implements Iterator, ArrayAccess, Countable
{
    /**
     * An array holding the file info properties
     * 
     * @var array
     */
    private $_props=array();
    /**
     * Numeric pointer used to keep track of position in array in order to 
     * implement the Iterator interface
     * 
     * @var int
     */
    private $_pointer=0;
    /**
     * A string with the clas name used to supply file info objects
     * 
     * @var string
     */
    private $_info_class=null;
    /**
     * A string with the clas name used to supply file objects
     * 
     * @var string
     */
    private $_file_class=null;
    
    /**
     * Constructor
     * 
     * @param string $file_name
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($file_name)
    {
        $file_name = trim((string) $file_name);
        
        if (!is_file($file_name) && !is_dir($file_name)) {
            $msg = "Can not get file info for file ";
            $msg .= $file_name.". File doesn't exist.";
            throw new RuntimeException($msg);
        }
        
        parent::__construct($file_name);
        
        $this->_populateProps();
        
        $this->setInfoClass(get_class($this));
        $this->setFileClass("PHPFrame_FileObject");
    }
    
    /**
     * Set info class
     * 
     * @param string $class_name
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setInfoClass($class_name)
    {
        $this->_info_class = trim((string) $class_name);
    }
    
    /**
     * Set file class
     * 
     * @param string $class_name
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setFileClass($class_name)
    {
        $this->_file_class = trim((string) $class_name);
    }

    /**
     * Get file info object
     * 
     * @access public
     * @return SplFileInfo
     * @since  1.0
     */
    public function getFileInfo()
    {
        $class_name = $this->_getInfoClass();
        
        return new $class_name(parent::getRealPath());
    }
    
    /**
     * Get file info object for path (parent directory)
     * 
     * @access public
     * @return SplFileInfo
     * @since  1.0
     */
    public function getPathInfo()
    {
        $class_name = $this->_getInfoClass();
        
        return new $class_name(parent::getPath());
    }
    
    /**
     * Open file
     * 
     * @param string   $open_mode
     * @param bool     $use_include_path
     * @param resource $context
     * 
     * @access public
     * @return PHPFrame_FileObject
     * @since  1.0
     */
    public function openFile($open_mode="r", $use_include_path=false, $context=null)
    {
        return new PHPFrame_FileObject(
            parent::getRealPath(),
            $open_mode, 
            $use_include_path,
            $context=null
        );
    }
    
    /*
     * Array Access interface implementation (offsetExists(), offsetGet(), 
     * offsetSet() and offsetUnset())
     */
    
    /**
     * Check whether a given offset exists in uderlying array
     * 
     * @param string $offset
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_props);
    }
    
    /**
     * Get a given offset from uderlying array
     * 
     * @param string $offset
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function offsetGet($offset)
    {
        return $this->_props[$offset];
    }

    /**
     * Set a given offset in uderlying array
     * 
     * @param string $offset
     * @param string $value
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function offsetSet($offset, $value)
    {
        $msg = "Can not set property. ";
        $msg .= get_class($this)."::".$offset." is read only.";
        throw new RuntimeException($msg);
        //$this->_props[$offset] = $value;
    }

    /**
     * Unset a given offset from in uderlying array
     * 
     * @param string $offset
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function offsetUnset($offset)
    {
        unset($this->_props[$offset]);
    }
    
    /* 
     * Countable interface implementation 
     */
    
    /**
     * Count
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function count()
    {
        return count($this->_props);
    }
    
    /* 
     * Iterator interface implementation 
     */
    
    /**
     * Get element at current pointer position
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function current()
    {
        return $this->_props[$this->key()];
    }

    /**
     * Move pointer one step forward
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function next()
    {
        $this->_pointer++;
    }
    
    /**
     * Get current key at pointer position
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function key()
    {
        $keys = array_keys($this->_props);
        $key = $keys[$this->_pointer];
        
        return $key;
    }
    
    /**
     * Rewind internal pointer
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function rewind()
    {
        $this->_pointer = 0;
    }

    /**
     * Check whether the current pointer position is valid
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function valid()
    {
        return ($this->_pointer < $this->count());
    }
    
    /**
     * Populate properties array using parent's methods
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _populateProps()
    {
        $reflectionObj = new ReflectionClass($this);
        $parent = $reflectionObj->getParentClass();
        $methods = $parent->getMethods();
        
        foreach ($methods as $method) {
            $method_name = $method->name;
            
            if (
                preg_match('/^get([a-zA-Z]+)$/', $method_name, $matches)
                && (
                    $method_name == "getLinkTarget" && $this->isLink()
                    || (
                        $method_name != "getLinkTarget"
                        && $method_name != "getFileInfo"
                        && $method_name != "getPathInfo"
                    )
                )
            ) {
                $this->_props[strtolower($matches[1])] = $this->$method_name();
            
            } elseif (
                preg_match('/^(is[a-zA-Z]+)$/', $method_name, $matches)
            ) {
                $this->_props[strtolower($matches[1])] = $this->$method_name();
            }
        }
    }
    
    /**
     * Get info class
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    private function _getInfoClass()
    {
        if (!is_null($this->_info_class)) {
            $class_name = $this->_info_class;
        } else {
            $class_name = get_class($this);
        }
        
        return $class_name;
    }
}
