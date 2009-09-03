<?php
/**
 * PHPFrame/Mapper/DomainObject.php
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
 * The Domain Object class is an abstract class that needs to be extended by objects 
 * that you want to use with the Mapper package.
 * 
 * To see an example of how to extend this class have a look at {@link PHPFrame_User}.
 * 
 * Domain objects implement the IteratorAggregate interface, which means that they 
 * can be iterated using the foreach construct and easily converted to an array by 
 * using PHPs iterator_to_array() function.
 * 
 * For example:
 * 
 * <code>
 * // Create a new user object (this object extends domain object)
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
abstract class PHPFrame_Mapper_DomainObject extends PHPFrame_Base_Object
    implements IteratorAggregate
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
    
    /**
     * Magic method to handle the cloning of domain objects
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
