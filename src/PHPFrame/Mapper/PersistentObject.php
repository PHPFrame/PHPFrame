<?php
/**
 * PHPFrame/Mapper/PersistentObject.php
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
 * The Persistent Object class is an abstract class that needs to be extended by objects 
 * that you want to use with the Mapper package.
 * 
 * To see an example of how to extend this class have a look at {@link PHPFrame_User}.
 * 
 * Persistent Objects implement the IteratorAggregate interface, which means that they 
 * can be iterated using the foreach construct and easily converted to an array by 
 * using PHPs iterator_to_array() function.
 * 
 * For example:
 * 
 * <code>
 * // Create a new user object (this object extends Persistent Object)
 * $user = new PHPFrame_User(array("username"=>"lupo"));
 * // Print the user oject as an array
 * print_r(iterator_to_array($user));
 * </code>
 * 
 * This will produce the following output:
 * 
 * <pre>
 * Array
 * (
 *  [groupid] => 0
 *  [username] => lupo
 *  [password] =>
 *  [firstname] =>
 *  [lastname] => 
 *  [email] => 
 *  [photo] =>
 *  [notifications] => 1
 *  [show_email] => 1
 *  [block] =>
 *  [last_visit] =>
 *  [activation] =>
 *  [params] => a:0:{}
 *  [deleted] => 
 *  [openid_urls] => a:0:{}
 *  [id] =>
 *  [created] =>
 *  [modified] => 
 *  )
 * <pre>
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @uses     IteratorAggregate
 * @since    1.0
 */
abstract class PHPFrame_PersistentObject extends PHPFrame_Object
    implements IteratorAggregate
{
    /**
     * The object id
     * 
     * @var int
     */
    protected $id=null;
    /**
     * UNIX timestamp of the object's last access date
     * 
     * @var string
     */
    protected $atime=null;
    /**
     * UNIX timestamp of the object's creation date
     * 
     * @var string
     */
    protected $ctime=null;
    /**
     * UNIX timestamp of the object's last modification date
     * 
     * @var string
     */
    protected $mtime=null;
    /**
     * User ID of object owner/creator
     * 
     * @var string
     */
    protected $owner=null;
    /**
     * Group ID of object
     * 
     * @var string
     */
    protected $group=null;
    /**
     * UNIX style permissions based on owner and group
     * 
     * @var string
     */
    protected $perms=null;
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
    
    /**
     * Magic method to handle the cloning of Persistent Objects
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __clone()
    {
        $this->id = null;
        $this->created = null;
        $this->modified = null;
    }
    
    /**
     * Get iterator
     * 
     * This method implements the IteratorAggregate interface and thus makes domain  
     * objects traversable, hooking to the foreach construct.
     * 
     * @access public
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    } 
    
    /**
     * Bind array to object
     * 
     * @param array $options
     * 
     * @access public
     * @return void
     * @since  1.0
     */
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
        $int = PHPFrame_Filter::validateInt($int);
        
        $this->id = $int;
    }
    
    /**
     * Get accessed timestamp
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getATime()
    {
        return $this->atime;
    }
    
    /**
     * Set accessed timestamp
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setATime($int)
    {
        $int = PHPFrame_Filter::validateInt($int);
        
        // Set property
        $this->atime = $int;
    }
    
    /**
     * Get created timestamp
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getCTime()
    {
        return $this->ctime;
    }
    
    /**
     * Set created timestamp
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setCTime($int)
    {
        $int = PHPFrame_Filter::validateInt($int);
        
        // Set property
        $this->ctime = $int;
    }
    
    /**
     * Get last modified timestamp
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getMTime()
    {
        return $this->mtime;
    }
    
    /**
     * Set last modified timestamp
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setMTime($int)
    {
        $int = PHPFrame_Filter::validateInt($int);
        
        // Set property
        $this->mtime = $int;
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
     * @access protected
     * @return array
     * @since  1.0
     */
    protected function toArray()
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
