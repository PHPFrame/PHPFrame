<?php
/**
 * PHPFrame/Filter/Validator.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Filter
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Validator class
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Validator
{
    /**
     * An array used to store field names and their filters.
     *
     * @var array
     */
    private $_filters = array();
    /**
     * Array containing names of fields that allow null value.
     *
     * @var array
     */
    private $_allow_null_fields = array();
    /**
     * The original values.
     *
     * @var array
     */
    private $_original_values = array();
    /**
     * The filtered values.
     *
     * @var array
     */
    private $_filtered_values = array();
    /**
     * Array used to store messages.
     *
     * @var array
     */
    private $_messages = array();
    /**
     * Boolean indicating whether we want validator to throw exceptions.
     *
     * @var bool
     */
    private $_throw_exceptions = false;
    /**
     * Default exception class used when not specified by filter.
     *
     * @var string
     */
    private $_exception_class = "Exception";

    /**
     * Constructor.
     *
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        //...
    }

    /**
     * Set a filter for a given field name in the validator.
     *
     * @param string          $field_name The name of the field this filter
     *                                    will apply to.
     * @param PHPFrame_Filter $filter     An object of type {@link PHPFrame_Filter}.
     * @param bool            $allow_null [Optional] Default value is FALSE.
     *
     * @return void
     * @since  1.0
     */
    public function setFilter(
        $field_name,
        PHPFrame_Filter $filter,
        $allow_null=false
    ) {
        if (!is_string($field_name) || strlen($field_name) < 1) {
            $msg  = get_class($this)."::setFilter() expects argument ";
            $msg .= "\$name to be of type string and not empty and got value ";
            $msg .= "'".$field_name."' of type ".gettype($field_name).".";
            throw new InvalidArgumentException($msg);
        }

        $this->_filters[$field_name] = $filter;

        // Before adding the field to the allow null fields array we remove to
        // avoid duplicated and start clean
        if (in_array($field_name, $this->_allow_null_fields)) {
            $this->_allow_null_fields = array_diff(
                $this->_allow_null_fields,
                array($field_name)
            );

            // reindex the array
            $this->_allow_null_fields = array_values($this->_allow_null_fields);

        }

        if ($allow_null) {
            $this->_allow_null_fields[]  = $field_name;
        }
    }

    /**
     * Get filter for a given field in the validator.
     *
     * @param string $field_name The name of the filter we want to get the
     *                           filter for.
     *
     * @return PHPFrame_Filter|null
     * @since  1.0
     */
    public function getFilter($field_name)
    {
        if (!is_string($field_name) || strlen($field_name) < 1) {
            $msg  = get_class($this)."::getFilter() expects argument ";
            $msg .= "\$name to be of type string and not empty and got value ";
            $msg .= "'".$field_name."' of type ".gettype($field_name).".";
            throw new InvalidArgumentException($msg);
        } elseif (!isset($this->_filters[$field_name])) {
            return null;
        }

        return $this->_filters[$field_name];
    }

    /**
     * Get filters.
     *
     * @return array
     * @since  1.0
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * Check whether a given field allows null value.
     *
     * @param string $field_name The field name we want to check.
     *
     * @return bool
     * @since  1.0
     */
    public function allowsNull($field_name)
    {
        return (in_array($field_name, $this->_allow_null_fields));
    }

    /**
     * Set whether or not the validator should throw exceptions.
     *
     * @param bool $bool Boolean indicating whether the validator should throw
     *                   exceptions. By default it is not set and validator
     *                   will simply return FALSE on failure.
     *
     * @return void
     * @since  1.0
     */
    public function throwExceptions($bool)
    {
        if (!is_bool($bool)) {
            $msg  = get_class($this)."::throwExceptions() expected argument ";
            $msg .= "\$bool to be of type 'bool' and got '".gettype($bool)."'.";
            throw new InvalidArgumentException($msg);
        }

        $this->_throw_exceptions = $bool;
    }

    /**
     * Set default exception class
     *
     * @param string $str The exception class name.
     *
     * @return void
     * @since  1.0
     */
    public function setExceptionClass($str)
    {
        if (!is_string($str)) {
            $msg  = get_class($this)."::setExceptionClass() expected argument ";
            $msg .= "\$str to be of type 'string' and got '".gettype($str)."'.";
            throw new InvalidArgumentException($msg);
        }

        $this->_exception_class = $str;
    }

    /**
     * Validate a value for a single field in validator.
     *
     * @param string $field_name The field name we want to validate.
     * @param mixed  $value      The value we want to validate.
     *
     * @return bool TRUE on success and FALSE on failure.
     * @since  1.0
     */
    public function validate($field_name, $value)
    {
        if (!is_string($field_name)) {
            $msg  = get_class($this)."::validate() expected argument ";
            $msg .= "\$field_name to be of type 'string' and got '";
            $msg .= gettype($field_name)."'.";
            throw new InvalidArgumentException($msg);
        }

        if (!isset($this->_filters[$field_name])) {
            $msg  = "No filter has been set for field '".$field_name."'.";
            throw new UnexpectedValueException($msg);
        }

        if (in_array($field_name, $this->_allow_null_fields)
            && is_null($value)
        ) {
            $this->_filtered_values[$field_name] = null;
            return true;
        }

        $filter = $this->_filters[$field_name];

        if ($filter instanceof PHPFrame_BoolFilter) {
            $null_on_fail = $filter->getOption("null_on_failure");
        } else {
            $null_on_fail = false;
        }

        $this->_filtered_values[$field_name] = $filter->process($value);

        if (count($filter->getMessages()) > 0
            && (($this->_filtered_values[$field_name] === false && !$null_on_fail)
            || (is_null($this->_filtered_values[$field_name]) && $null_on_fail))
        ) {
            $msg = "Failed to validate field '".$field_name."'. ";
            $last_message = end($filter->getMessages());
            $this->fail($msg.$last_message[0], $last_message[1]);
            return false;
        }

        return true;
    }

    /**
     * Validate all fields and return.
     *
     * @param array $assoc An associative array containing the field names and
     *                     the values to process.
     *
     * @return mixed The filtered array or FALSE on failure.
     * @since  1.0
     */
    public function validateAll(array $assoc)
    {
        $array_obj = new PHPFrame_Array($assoc);
        if (!$array_obj->isAssoc()) {
            $msg  = get_class($this)."::validateAll() expected argument assoc ";
            $msg .= "to be of an associative array and got a numerically ";
            $msg .= "indexed one.";
            throw new InvalidArgumentException($msg);
        }

        $this->_original_values = $assoc;
        $this->_filtered_values = array();

        foreach ($assoc as $key=>$value) {
            if (array_key_exists($key, $this->_filters)
                && !$this->validate($key, $value)
            ) {
                return false;
            }
        }

        return $this->getFilteredValues();
    }

    /**
     * Get original values array.
     *
     * @return array
     * @since  1.0
     */
    public function getOriginalValues()
    {
        return $this->_original_values;
    }

    /**
     * Get filtered values array.
     *
     * @return array
     * @since  1.0
     */
    public function getFilteredValues()
    {
        return $this->_filtered_values;
    }

    /**
     * Get original value.
     *
     * @param string $field_name The field name we want to get the value for.
     *
     * @return mixed
     * @since  1.0
     */
    public function getOriginalValue($field_name)
    {
        return $this->_original_values[$field_name];
    }

    /**
     * Get filtered value.
     *
     * @param string $field_name The field name we want to get the value for.
     *
     * @return mixed
     * @since  1.0
     */
    public function getFilteredValue($field_name)
    {
        return $this->_filtered_values[$field_name];
    }

    /**
     * Get messages array.
     *
     * @return array
     * @since  1.0
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Notify failure.
     *
     * @param string $str             The failure message.
     * @param string $exception_class [Optional] Specialised exception class.
     *
     * @return void
     * @since  1.0
     */
    protected function fail($str, $exception_class=null)
    {
        if (!is_string($str)) {
            $msg  = get_class($this)."::fail() expected argument \$str ";
            $msg .= "to be of type 'string' and got '".gettype($str)."'.";
            throw new InvalidArgumentException($msg);
        }

        if (is_null($exception_class)) {
            $exception_class = $this->_exception_class;
        }

        $this->_messages[] = array($str, $exception_class);

        if ($this->_throw_exceptions) {
            throw new $exception_class($str);
        }
    }
}
