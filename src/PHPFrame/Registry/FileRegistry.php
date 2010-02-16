<?php
/**
 * PHPFrame/Registry/FileRegistry.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Registry
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * File based registry
 * 
 * @category PHPFrame
 * @package  Registry
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Registry
 * @uses     PHPFrame_Permissions, PHPFrame_Libraries, 
 *           PHPFrame_Features, PHPFrame_Filesystem
 * @since    1.0
 */
class PHPFrame_FileRegistry extends PHPFrame_Registry
{
    /**
     * SplFileObject object representing the cache file on disk
     * 
     * @var SplFileObject
     */
    private $_file_obj = null;
    /**
     * An array to store application registry data set on runtime
     * 
     * @var array
     */
    private $_data = array();
    /**
     * A boolean to indicate whether the data has changed since it was last 
     * written to file
     * 
     * @var bool
     */
    private $_dirty = false;
    
    /**
     * Constructor
     * 
     * @param string $cache_file
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($cache_file) 
    {
        $cache_file = trim((string) $cache_file);
        
        // Read data from cache
        if (is_file($cache_file)) {
            // Open cache file in read/write mode
            $this->_file_obj = new SplFileObject($cache_file, "r+");
            // Load data from cache file
            $file_contents = implode("\n", iterator_to_array($this->_file_obj));
            $this->_data   = unserialize($file_contents);
        } else {
            // Open cache file in write mode
            $this->_file_obj = new SplFileObject($cache_file, "w");
        }
    }
    
    /**
     * Destructor
     * 
     * The destructor method will be called as soon as all references to a 
     * particular object are removed or when the object is explicitly destroyed 
     * or in any order in shutdown sequence.
     * 
     * @return void
     * @since  1.0
     */
    public function __destruct()
    {
        if ($this->isDirty()) {
            $this->_file_obj->rewind();
            $this->_file_obj->fwrite(serialize($this->_data));
        }
    }
    
    /**
     * Implementation of IteratorAggregate interface
     * 
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }
    
    /**
     * Get an application registry variable
     * 
     * @param string $key
     * @param mixed  $default_value
     * 
     * @return mixed
     * @since  1.0
     */
    public function get($key, $default_value=null) 
    {
        $key = trim((string) $key);
        
        // Set default value if appropriate
        if (!isset($this->_data[$key]) && !is_null($default_value)) {
            $this->_data[$key] = $default_value;
            
            // Mark data as dirty
            $this->markDirty();
        }
        
        // Return null if index is not defined
        if (!isset($this->_data[$key])) {
            return null;
        }
        
        return $this->_data[$key];
    }
    
    /**
     * Set an application registry variable
     * 
     * @param string $key
     * @param mixed  $value
     * 
     * @return void
     * @since  1.0
     */
    public function set($key, $value) 
    {
        $key = trim((string) $key);
        
        $this->_data[$key] = $value;
        
        // Mark data as dirty
        $this->markDirty();
    }
    
    /**
     * Mark the application data as dirty (it needs writting to file)
     * 
     * @return void
     * @since  1.0
     */
    public function markDirty()
    {
        $this->_dirty = true;
    }
    
    /**
     * Is the application registry data dirty?
     * 
     * @return bool
     * @since  1.0
     */
    public function isDirty()
    {
        return $this->_dirty;
    }
}
