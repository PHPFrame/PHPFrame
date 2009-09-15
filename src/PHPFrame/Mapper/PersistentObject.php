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
 * The Persistent Object class is an abstract class that needs to be extended by 
 * objects that you want to use with the Mapper package.
 * 
 * To see an example of how to extend this class have a look at 
 * {@link PHPFrame_User}.
 * 
 * Persistent Objects implement the IteratorAggregate interface, which means 
 * that they can be iterated using the foreach construct and easily converted to 
 * an array by using PHPs iterator_to_array() function.
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
     * @var int
     */
    protected $atime=null;
    /**
     * UNIX timestamp of the object's creation date
     * 
     * @var int
     */
    protected $ctime=null;
    /**
     * UNIX timestamp of the object's last modification date
     * 
     * @var int
     */
    protected $mtime=null;
    /**
     * User ID of object owner/creator
     * 
     * @var int
     */
    protected $owner=null;
    /**
     * Group ID of object
     * 
     * @var int
     */
    protected $group=null;
    /**
     * UNIX style permissions based on owner and group
     * 
     * @var int
     */
    protected $perms=null;
    /**
     * Serialised string respresenting clean state. This is used to check if the
     * current state is "dirty" if it has changed since last marked clean.
     * 
     * @var string
     */
    private $_clean_state;
    
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
        
        $this->markClean();
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
        $this->id    = null;
        $this->atime = null;
        $this->ctime = null;
        $this->mtime = null;
    }
    
    /**
     * Get iterator
     * 
     * This method implements the IteratorAggregate interface and thus makes 
     * domain objects traversable, hooking to the foreach construct.
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
                
                // Get parameters and ignore if parameter is array and the 
                // value is not
                $params = $setter_method->getParameters();
                if ($params[0]->isArray() && !is_array($value)) {
                    continue;
                }
                
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
        if (empty($int)) {
            return;
        }
        
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
        if (empty($int)) {
            return;
        }
        
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
     * Get owner
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getOwner()
    {
        return $this->owner;
    }
    
    /**
     * Set owner
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setOwner($int)
    {
        $int = PHPFrame_Filter::validateInt($int);
        
        // Set property
        $this->owner = $int;
    }
    
    /**
     * Get group ownership
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getGroup()
    {
        return $this->group;
    }
    
    /**
     * Set group ownership
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setGroup($int)
    {
        $int = PHPFrame_Filter::validateInt($int);
        
        // Set property
        $this->group = $int;
    }
    
    /**
     * Get permissions
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getPerms()
    {
        return $this->perms;
    }
    
    /**
     * Set permissions
     * 
     * @param int $int
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setPerms($int)
    {
        $int = PHPFrame_Filter::validateInt($int);
        
        // Set property
        $this->perms = $int;
    }
    
    /**
     * Mark object as clean
     * 
     * @return void
     */
    public function markClean()
    {
        $this->_clean_state = serialize($this->toArray());
    }
    
    /**
     * Is object dirty? If it is it means that it has changed since it was last 
     * persisted.
     * 
     * @return void
     */
    public function isDirty()
    {
        return !($this->_clean_state == serialize($this->toArray()));
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
            if ($key != "_clean_state") {
                $array[$key] = $value;
            }
        }
        
        return $array;
    }
}
