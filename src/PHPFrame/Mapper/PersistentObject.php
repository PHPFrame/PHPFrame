<?php
/**
 * PHPFrame/Mapper/PersistentObject.php
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
 * $user = new PHPFrame_User(array("email"=>"lupo@e-noise.com"));
 * // Print the user oject as an array
 * print_r(iterator_to_array($user));
 * </code>
 *
 * This will produce the following output:
 *
 * <pre>
 * Array
 * (
 *  [group_id] => 0
 *  [email] => lupo@e-noise.com
 *  [password] =>
 *  [block] =>
 *  [last_visit] =>
 *  [params] => a:0:{}
 *  [deleted] =>
 *  [id] =>
 *  [ctime] =>
 *  [mtime] =>
 *  [owner] => 0
 *  [group] => 0
 *  [perms] => 664
 *  )
 * </pre>
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
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
     * Constructor. All options are optional when creating new objects. IDs are
     * automatically generated when saving the objects using a mapper object
     * (see {@link PHPFrame_Mapper}).
     *
     * @param array $options An associative array containing keys with the
     *                       field names and values used for this fields when
     *                       constructing the object.
     *                       Option keys:
     *                       - id (int) The object ID. Omit this option when
     *                         creating new objects. A new ID will be generated
     *                         after saving the object using a mapper object.
     *                       - ctime (int) The created time (UNIX timestamp).
     *                       - mtime (int) The modified time (UNIX timestamp).
     *                       - owner (int) The user ID of the object's owner
     *                       - group (int) The group ID associated with the object.
     *                       - perms (int) The permissions settings (UNIX style)
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        // Add the base fields
        $this->addField("id", null, true, new PHPFrame_IntFilter());
        $this->addField("ctime", null, true, new PHPFrame_IntFilter());
        $this->addField("mtime", null, true, new PHPFrame_IntFilter());
        $this->addField(
            "owner",
            0,
            false,
            new PHPFrame_IntFilter()
        );
        $this->addField(
            "group",
            0,
            false,
            new PHPFrame_IntFilter()
        );
        $this->addField("perms", 664, false, new PHPFrame_IntFilter());

        // Process options argument if passed
        if (!is_null($options)) {
            $this->bind($options);
        }
    }

    /**
     * Magic method to handle the serialisation of objects
     *
     * @return array
     * @since  1.0
     */
    public function __sleep()
    {
        $this->markClean();

        return array("fields");
    }

    /**
     * Magic method to handle the unserialisation of objects
     *
     * @return void
     * @since  1.0
     */
    public function __wakeup()
    {
        $this->__construct($this->fields);
    }

    /**
     * Magic method to handle the cloning of Persistent Objects
     *
     * @return void
     * @since  1.0
     */
    public function __clone()
    {
        $this->fields["id"]    = null;
        $this->fields["ctime"] = null;
        $this->fields["mtime"] = null;
    }

    /**
     * Magic method to handle calls to undeclared getter/setter methods.
     *
     * @param string $name The method name invoked.
     * @param array  $args Array containing arguments passed to the method.
     *
     * @return mixed Return type will depend on the 'actual' method being invoked
     * @throws BadMethodCallException if can not resolve method name.
     * @since  1.0
     */
    public function __call($name, $args)
    {
        // Convert camel case to underscores
        $field = strtolower(preg_replace("/([A-Z][a-z]+)/", "_$1", $name));

        if (!array_key_exists($field, $this->fields)) {
            $msg  = "Wrong method call. ".get_class($this)." does not have ";
            $msg .= "any method called '".$name."' or field with the ";
            $msg .= "name of '".$field."'.";
            throw new BadMethodCallException($msg);
        }

        if (is_array($args) && count($args) > 0) {
            if (array_key_exists($field, $this->getFilters())) {
                $this->fields[$field] = $this->validate($field, $args[0]);
            } else {
                $this->fields[$field] = $args[0];
            }
        }

        return $this->fields[$field];
    }

    /**
     * Add a field in persistent object (stored in internal array)
     *
     * @param string          $name       The field name.
     * @param mixed           $def_value  [Optional] Default value.
     * @param bool            $allow_null [Optional] Whether or not the field
     *                                    should allow null values.
     * @param PHPFrame_Filter $filter     [Optional] An instance of
     *                                    {@link PHPFrame_Filter} used to
     *                                    validate the field's value.
     *
     * @return void
     * @since  1.0
     */
    protected function addField(
        $name,
        $def_value=null,
        $allow_null=true,
        PHPFrame_Filter $filter=null
    ) {
        if (!is_string($name) || strlen($name) < 1) {
            $msg  = get_class($this)."::addField() expects argument ";
            $msg .= "\$name to be of type string and not empty and got value ";
            $msg .= "'".$name."' of type ".gettype($name);
            throw new InvalidArgumentException($msg);
        }

        if (is_null($filter)) {
            $filter = new PHPFrame_StringFilter();
        }

        // Store filter in validator
        $this->_getValidator()->setFilter($name, $filter, $allow_null);

        // Set key with default value in internal array
        if (!is_null($def_value)) {
            $this->fields[$name] = $this->validate($name, $def_value);
        } else {
            $this->fields[$name] = null;
        }
    }

    /**
     * Get filters
     *
     * @return array
     * @since  1.0
     */
    public function getFilters()
    {
        return $this->_getValidator()->getFilters();
    }

    /**
     * Check whether a given field allows null value
     *
     * @param string $field_name The field name.
     *
     * @return bool
     * @since  1.0
     */
    public function allowsNull($field_name)
    {
        return $this->_getValidator()->allowsNull($field_name);
    }

    /**
     * Validate value for a given field using validator
     *
     * @param string $field_name The field name.
     * @param mixed  $value      The field value.
     *
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
     * Validate all fields and throw exception on failure.
     *
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
     * @param PHPFrame_User $user Instance of {@link PHPFrame_User}.
     *
     * @return bool
     * @since  1.0
     */
    public function canRead(PHPFrame_User $user)
    {
        return $this->_checkPerms($user, 4);
    }

    /**
     * Can user write this object?
     *
     * @param PHPFrame_User $user Instance of {@link PHPFrame_User}.
     *
     * @return bool
     * @since  1.0
     */
    public function canWrite(PHPFrame_User $user)
    {
        return $this->_checkPerms($user, 6);
    }

    /**
     * Get iterator
     *
     * This method implements the IteratorAggregate interface and thus makes
     * domain objects traversable, hooking to the foreach construct.
     *
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
     * @param array $options An associative array with the field names as keys.
     *                       Unknown keys will be ignored.
     *
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
            $setter_name = ucwords(str_replace("_", " ", $key));
            $setter_name = str_replace(" ", "", $setter_name);

            if ($reflectionObj->hasMethod($setter_name)) {
                // Get reflection method for setter
                $setter_method = $reflectionObj->getMethod($setter_name);

                // Get parameters and ignore if parameter is array and the
                // value is not
                $params = $setter_method->getParameters();
                if ($params instanceof ReflectionParameter
                    && $params[0]->isArray()
                    && !is_array($value)
                ) {
                    continue;
                }

                // Invoke setter if it takes at least one argument
                if ($setter_method->getNumberOfParameters() > 0) {
                    $this->$setter_name($value);
                }
            } elseif (array_key_exists($key, $this->fields)) {
                $this->$key($value);
            }
        }
    }

    /**
     * Get/set id.
     *
     * @param int $int [Optional] The object ID.
     *
     * @return int
     * @since  1.0
     */
    public function id($int=null)
    {
        if (!is_null($int)) {
            $this->fields["id"] = $this->validate("id", $int);
        }

        return $this->fields["id"];
    }

    /**
     * Get/set created timestamp.
     *
     * @param int $int [Optional] The created time as a UNIX timestamp.
     *
     * @return int
     * @since  1.0
     */
    public function ctime($int=null)
    {
        if (!is_null($int)) {
            $this->fields["ctime"] = $this->validate("ctime", $int);
        }

        return $this->fields["ctime"];
    }

    /**
     * Get/set last modified timestamp.
     *
     * @param int $int The modified time as a UNIX timestamp.
     *
     * @return int
     * @since  1.0
     */
    public function mtime($int=null)
    {
        if (!is_null($int)) {
            $this->fields["mtime"] = $this->validate("mtime", $int);
        }

        return $this->fields["mtime"];
    }

    /**
     * Get/set owner.
     *
     * @param int $int [Optional] The user ID of the object's owner.
     *
     * @return int
     * @since  1.0
     */
    public function owner($int=null)
    {
        if (!is_null($int)) {
            $this->fields["owner"] = $this->validate("owner", $int);
        }

        return $this->fields["owner"];
    }

    /**
     * Get/set group ownership.
     *
     * @param int $int [Optional] The group ID of the object's group.
     *
     * @return int
     * @since  1.0
     */
    public function group($int=null)
    {
        if (!is_null($int)) {
            $this->fields["group"] = $this->validate("group", $int);
        }

        return $this->fields["group"];
    }

    /**
     * Get/set permissions.
     *
     * @param int $int [Optional] UNIX style permissions.
     *
     * @return int
     * @since  1.0
     */
    public function perms($int=null)
    {
        if (!is_null($int)) {
            $this->fields["perms"] = $this->validate("perms", $int);
        }

        return $this->fields["perms"];
    }

    /**
     * Get validator object.
     *
     * @return PHPFrame_Validator
     * @since  1.0
     */
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
     * @param PHPFrame_User $user         Reference to the user object we want
     *                                    to check permissions for.
     * @param int           $access_level The access level we want to check.
     *
     * @return bool
     * @since  1.0
     */
    private function _checkPerms(PHPFrame_User $user, $access_level)
    {
        preg_match('/^(\d)(\d)(\d)$/', $this->perms(), $matches);
        $owner_access = $matches[1];
        $group_access = $matches[2];
        $world_access = $matches[3];

        // Bypass check for admin group
        if ($user->groupId() == 1) {
            return true;
        }

        // Check user access
        if ($world_access >= $access_level) {
            return true;
        }

        // Check user access
        if ($user->id() == $this->owner()
            && $owner_access >= $access_level
        ) {
            return true;
        }

        // Check group access
        if ($user->groupId() == $this->group()
            && $group_access >= $access_level
        ) {
            return true;
        }

        // Check secondary group
        $user_params = $user->params();
        if (is_array($user_params)
            && array_key_exists("secondary_groups", $user_params)
        ) {
            $secondary_groups = explode(",", $user_params["secondary_groups"]);
            if (is_array($secondary_groups) && count($secondary_groups) > 0) {
                foreach ($secondary_groups as $secondary_group) {
                    if ($secondary_group == $this->group()
                        && $group_access >= $access_level
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
