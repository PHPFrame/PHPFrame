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
     * List of known types for fields
     * 
     * @var array
     */
    private $_field_types = array("int", "varchar", "enum", "text");
    /**
     * An array containig filters used to validate data in each field
     *  
     * @var array
     */
    private $_filters = array();
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
        $this->addFilter("id",    "int", null, null, false);
        $this->addFilter("atime", "int", null, null, false, 0);
        $this->addFilter("ctime", "int", null, null, false, time());
        $this->addFilter("mtime", "int", null, null, false, time());
        $this->addFilter("owner", "int", null, null, false, 1);
        $this->addFilter("group", "int", null, null, false, 1);
        $this->addFilter("perms", "int", null, null, false, 664);
        
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
        $props_array = get_object_vars($this);
        $array       = array();
        
        foreach ($props_array as $key=>$value) {
            if (
                $key != "_field_types" 
                && $key != "_filters" 
                && $key != "_clean_state"
            ) {
                $array[$key] = $value;
            }
        }
        
        return new ArrayIterator($array);
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
        
        $this->id = $this->validate("id", $int);
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
            $int = 0;
        }
        
        $this->atime = $this->validate("atime", $int);
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
        $this->ctime = $this->validate("ctime", $int);
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
        $this->mtime = $this->validate("mtime", $int);
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
        $this->owner = $this->validate("owner", $int);
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
        $this->group = $this->validate("group", $int);
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
        $this->perms = $this->validate("perms", $int);
    }
    
    /**
     * Get array with all filters
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getFilters()
    {
        return $this->_filters;
    }
    
    /**
     * Get filter for a given field
     * 
     * @param string $key The field name
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getFilter($key)
    {
        if (!isset($this->_filters[$key])) {
            return null;
        }
        
        return $this->_filters[$key];
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
     * Add a field filter
     *  
     * @param string    $field
     * @param string    $type
     * @param int|array $max_length
     * @param int       $min_length
     * @param bool      $allow_null
     * @param mixed     $def_value
     * @param string    $regex
     * 
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function addFilter(
        $field, 
        $type, 
        $max_length=null, 
        $min_length=null, 
        $allow_null=false,
        $def_value=null,
        $regex=null
    )
    {
        if (!in_array($type, $this->_field_types)) {
            $msg  = "Argument \$type must be one of the following values: ";
            $msg .= "'".implode("', '",$this->_field_types)."'. Passed value: ";
            $msg .= "'".$type."'.";
            throw new DomainException($msg);
        }
        
        if ($type == "varchar" && empty($max_length)) {
            $msg  = "Argument \$max_length can not be empty if argument \$type";
            $msg .= " is 'varchar'.";
            throw new InvalidArgumentException($msg);
        }
        
        if ($type == "enum" && !is_array($max_length)) {
            $msg  = "Argument \$max_length must be an array if argument \$type";
            $msg .= " is 'enum'.";
            throw new InvalidArgumentException($msg);
        } elseif ($type != "enum") {
            $max_length = (int) $max_length;
        }
        
        $this->_filters[$field] = array(
            "type"       => (string) $type,
            "max_length" => $max_length,
            "min_length" => (int)    $min_length,
            "allow_null" => (bool)   $allow_null,
            "def_value"  => $def_value,
            "regex"      => (string) $regex
        );
    }
    
    /**
     * Validate a value for a given field
     * 
     * @param string $field
     * @param mixed  $value
     * 
     * @access protected
     * @return mixed
     * @since  1.0
     */
    protected function validate($field, $value)
    {
        $filter = $this->getFilter($field);
        
        if ($field == "id" && is_null($value)) {
            return;
        }
        
        if (
            is_null($value) 
            && isset($filter["def_value"]) 
            && !is_null($filter["def_value"])
        ) {
            $value = $filter["def_value"];
        }
        
        if (
            is_null($value) 
            && isset($filter["allow_null"]) 
            && !$filter["allow_null"]
        ) {
            $msg  = "Field '".$field."' can not be null. Null passed and no ";
            $msg .= "default value has been defined in filter.";
            throw new RuntimeException($msg);
        } elseif (is_null($value)  && $filter["allow_null"]) {
            return $value;
        }
        
        if (is_array($filter) && isset($filter["type"])) {
            switch ($filter["type"]) {
                case "int" : 
                    $value = PHPFrame_Filter::validateInt($value);
                    break;
                case "varchar" : 
                    $pattern = '/^.{'.$filter["min_length"].','.$filter["max_length"].'}$/';
                    $value = PHPFrame_Filter::validateRegExp($value, $pattern);
                    break;
                case "enum" :
                    $value = PHPFrame_Filter::validateEnum($value, $filter["max_length"]);
                    break; 
                case "text" :
                    $value = PHPFrame_Filter::validateDefault($value);
                    break;
            }
        }
        
        if (isset($filter["regex"]) && !empty($filter["regex"])) {
            switch ($filter["regex"]) {
                case "email" :
                    $value = PHPFrame_Filter::validateEmail($value);
                    break;
                case "url" :
                    $value = PHPFrame_Filter::validateURL($value);
                    break;
                case "ip" :
                    $value = PHPFrame_Filter::validateIP($value);
                    break;
                default :
                    $value = PHPFrame_Filter::validateRegExp($value, $filter["regex"]);
                    break;
            }
        }
        
        return $value;
    }
    
    /**
     * Validate all fields in object
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function validateAll()
    {
        foreach ($this as $key=>$value) {
            $setter = "set".str_replace("_", "", $key);
            $this->$setter($this->validate($key, $value));
        }
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
