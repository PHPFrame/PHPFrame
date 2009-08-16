<?php
/**
 * PHPFrame/FS/FileInfo.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage FS
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Logger.php 320 2009-07-28 17:28:32Z luis.montero@e-noise.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * File Info Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage FS
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        SplFileInfo, PHPFrame_FS_FileObj
 * @since      1.0
 */
class PHPFrame_FS_FileInfo 
    extends SplFileInfo implements Iterator, ArrayAccess, Countable
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
     * A flag to indicate whether properties array should be processed for user
     * friendly values.
     * 
     * @var bool
     */
    private $_human_readable=false;
    
	/**
	 * Constructor
	 * 
	 * @param string $file_name
	 * @param bool   $human_readable
	 * 
	 * @access public
	 * @return void
	 * @since  1.0
	 */
	public function __construct($file_name, $human_readable=false)
	{
	    $file_name = (string) trim($file_name);
	    $this->_human_readable = (boolean) $human_readable;
	    
	    if (!is_file($file_name) && !is_dir($file_name)) {
	        $msg = "Can not get file info for file ";
	        $msg .= $file_name.". File doesn't exist.";
	        throw new Exception($msg);
	    }
	    
	    parent::__construct($file_name);
	    
	    $this->_populateProps();
	    
	    
	    if ($this->_human_readable) {
	        $this->_makeHumanReadable();
	    }
	    
	    $this->setInfoClass(get_class($this));
	    $this->setFileClass("PHPFrame_FS_FileObj");
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
	    $this->_info_class = (string) trim($class_name);
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
	    $this->_file_class = (string) trim($class_name);
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
	    
	    return new $class_name(parent::getRealPath(), $this->_human_readable);
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
	    
	    return new $class_name(parent::getPath(), $this->_human_readable);
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
	    throw new PHPFrame_Exception($msg);
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
	
	/**
	 * Process properties array to make human readable (user friendly)
	 * 
	 * @access private
	 * @return void
	 * @since  1.0
	 */
	private function _makeHumanReadable()
	{
	    foreach ($this->_props as $key=>$value) {
	        if ($key == "size") {
	            $value = PHPFrame_Base_Number::bytes($value);
	        } elseif (preg_match('/time$/', $key)) {
	            $value = date("Y-m-d H:i:s", $value);
	        } elseif ($key == "perms") {
	            $value = substr(sprintf('%o', $value), -4);
	        }
	        
	        $this->_props[$key] = $value;
	    }
	}
}
