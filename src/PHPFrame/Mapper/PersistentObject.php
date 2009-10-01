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
     * Internal array to store fields data
     * 
     * @var array
     */
    protected $fields = array();
    /**
     * An validator object used to validate fields
     *  
     * @var PHPFrame_Validator
     */
    private $_validator;
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
        // Add the base fields
        $this->addField("id",    null,   true,  new PHPFrame_IntFilter());
        $this->addField("atime", null,   true,  new PHPFrame_IntFilter());
        $this->addField("ctime", time(), true,  new PHPFrame_IntFilter());
        $this->addField("mtime", time(), true,  new PHPFrame_IntFilter());
        $this->addField("owner", 1,      false, new PHPFrame_IntFilter());
        $this->addField("group", 1,      false, new PHPFrame_IntFilter());
        $this->addField("perms", 664,    false, new PHPFrame_IntFilter());
        
        // Set object ownership to current user if applicable
        if (PHPFrame::getRunLevel() > 1 && PHPFrame::Session()->isAuth()) {
            $this->setOwner(PHPFrame::Session()->getUserId());
            $this->setGroup(PHPFrame::Session()->getGroupId());
            $this->setPerms(664);
        }
        
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
        $this->fields["id"]    = null;
        $this->fields["atime"] = null;
        $this->fields["ctime"] = null;
        $this->fields["mtime"] = null;
    }
    
    protected function addField(
        $name, 
        $def_value=null, 
        $allow_null=true, 
        PHPFrame_Filter $filter=null
    )
    {
        if (!is_string($name) || strlen($name) < 1) {
            $msg  = get_class($this)."::addField() expects argument ";
            $msg .= "\$name to be of type string and not empty and got value ";
            $msg .= "'".$name."' of type ".gettype($name);
            throw new InvalidArgumentException($msg);
        }
        
        // Set key with default value in internal array
        $this->fields[$name] = $def_value;
        // Store filter in validator
        $this->_getValidator()->setFilter($name, $filter, $allow_null);
    }
    
    /**
     * Validate value for a given field using validator
     * 
     * @param string $field_name
     * @param mixed  $value
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function validate($field_name, $value)
    {
        if (!$this->_getValidator()->validate($field_name, $value)) {
            $last_message = end($this->_getValidator()->getMessages());
            if (isset($last_message[1]) && class_exists($last_message[1])) {
                $exception_class = $last_message[1];
            } else {
                $exception_class = "Exception";
            }
            throw new $exception_class($last_message[0]);
        }
        
        return $this->_getValidator()->getFilteredValue($field_name);
    }
    
    /**
     * Validate all fields and throw exception on failure
     * 
     * @param array $assoc An associative array containing the field names and 
     *                     the values to process.
     * 
     * @access public
     * @return mixed The filtered array or FALSE on failure
     * @since  1.0
     */
    public function validateAll()
    {
        $this->_getValidator()->throwExceptions(true);
        
        return $this->_getValidator()->validateAll(iterator_to_array($this));
    }
    
    /**
     * Mark object as clean
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function markClean()
    {
        $this->_clean_state = serialize(iterator_to_array($this));
    }
    
    /**
     * Is object dirty? If it is it means that it has changed since it was last 
     * persisted.
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function isDirty()
    {
        return !($this->_clean_state == serialize(iterator_to_array($this)));
    }
    
    /**
     * Can user read this object?
     * 
     * @param PHPFrame_user $user
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function canRead(PHPFrame_user $user)
    {
        return $this->_checkPerms($user, 4);
    }
    
    /**
     * Can user write this object?
     * 
     * @param PHPFrame_user $user
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function canWrite(PHPFrame_user $user)
    {
        return $this->_checkPerms($user, 6);
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
        return new ArrayIterator($this->fields);
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
        return $this->fields["id"];   
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
        
        $this->fields["id"] = $this->validate("id", $int);
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
        return $this->fields["atime"];
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
            $int = 0;
        }
        
        $this->fields["atime"] = $this->validate("atime", $int);
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
        return $this->fields["ctime"];
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
        $this->fields["ctime"] = $this->validate("ctime", $int);
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
        return $this->fields["mtime"];
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
        $this->fields["mtime"] = $this->validate("mtime", $int);
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
        return $this->fields["owner"];
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
        $this->fields["owner"] = $this->validate("owner", $int);
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
        return $this->fields["group"];
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
        $this->fields["group"] = $this->validate("group", $int);
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
        return $this->fields["perms"];
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
        $this->fields["perms"] = $this->validate("perms", $int);
    }
    
    private function _getValidator()
    {
        if (!$this->_validator instanceof PHPFrame_Validator) {
            $this->_validator = new PHPFrame_Validator();
        }
        
        return $this->_validator;
    }
    
    /**
     * Check permissions
     * 
     * @param PHPFrame_user $user
     * @param int           $access_level
     * 
     * @access private
     * @return bool
     * @since  1.0
     */
    private function _checkPerms(PHPFrame_user $user, $access_level)
    {
        preg_match('/^(\d)(\d)(\d)$/', $this->getPerms(), $matches);
        $owner_access = $matches[1];
        $group_access = $matches[2];
        $world_access = $matches[3];
        
        // Check user access
        if ($world_access >= $access_level) {
            return true;
        }
        
        // Check user access
        if (
            $user->getId() == $this->getOwner() 
            && $owner_access >= $access_level
        ) {
            return true;
        }
        
        // Check group access
        if (
            $user->getGroupId() == $this->getGroup() 
            && $group_access >= $access_level
        ) {
            return true;
        }
        
        return false;
    }
}
