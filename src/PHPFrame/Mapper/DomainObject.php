<?php
/**
 * PHPFrame/Mapper/DomainObject.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * DomainObject Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Mapper
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
abstract class PHPFrame_Mapper_DomainObject extends PHPFrame_Base_Object
{
    /**
     * The object id
     * 
     * @var int
     */
    protected $id=null;
    /**
     * Date the object was first stored (in MySQL Datetime format)
     * 
     * @var string
     */
    protected $created=null;
    /**
     * Last modified datetime (in MySQL Datetime format)
     * 
     * @var string
     */
    protected $modified=null;
    /**
     * Boolean indicating whether the object is dirty (has changed since it was 
     * last stored).
     * 
     * @var bool
     */
    private $_dirty=false;
    
    /**
     * Constructor
     * 
     * @param array $options
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        // Process options argument if passed
        if (!is_null($options)) {
            $this->bind($options);
        }
    }
    
    public function __clone()
    {
        $this->id = null;
        $this->created = null;
        $this->modified = null;
    }
    
    public function bind(array $options)
    {
        // Create reflection object
        $reflectionObj = new ReflectionClass($this);
        
        foreach ($options as $key=>$value) {
            if (is_null($value)) {
                continue;
            }
            
            // Build string with setter name
            $setter_name = "set".ucwords(str_replace("_", " ", $key));
            $setter_name = str_replace(" ", "", $setter_name);
            
            if ($reflectionObj->hasMethod($setter_name)) {
                // Get reflection method for setter
                $setter_method = $reflectionObj->getMethod($setter_name);
                
                // Invoke setter if it takes only one required argument
                if ($setter_method->getNumberOfRequiredParameters() == 1) {
                    $this->$setter_name($value);
                }
            }
        }
    }
    
    /**
     * Get id
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getId()
    {
        return $this->id;   
    }
    
    /**
     * Set id
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setId($int)
    {
        $int = PHPFrame_Utils_Filter::validateInt($int);
        
        $this->id = $int;
    }
    
    /**
     * Get created datetime
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getCreated()
    {
        return $this->created;
    }
    
    /**
     * Set created datetime
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setCreated($str)
    {
        //echo $str; exit;
        $str = PHPFrame_Utils_Filter::validateDateTime($str);
        
        // Set property
        $this->created = $str;
    }
    
    /**
     * Get last modified datetime
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getModified()
    {
        return $this->modified;
    }
    
    /**
     * Set last modified datetime
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setModified($str)
    {
        //echo $str; exit;
        $str = PHPFrame_Utils_Filter::validateDateTime($str);
        
        // Set property
        $this->modified = $str;
    }
    
    /**
     * Mark object as clean
     * 
     * @return void
     */
    public function markClean()
    {
        $this->_dirty = false;
    }
    
    /**
     * Mark object as dirty
     * 
     * @return void
     */
    public function markDirty()
    {
        $this->_dirty = false;
    }
    
    /**
     * Is object dirty? If it is it means that it has changed since it was last 
     * persisted.
     * 
     * @return void
     */
    public function isDirty()
    {
        return $this->_dirty;
    }
    
    /**
     * Get object as array
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function toArray()
    {
        $array = array();
        $props_array = get_object_vars($this);
        
        foreach ($props_array as $key=>$value) {
            if ($key != "_dirty") {
                $array[$key] = $value;
            }
        }
        
        return $array;
    }
}
