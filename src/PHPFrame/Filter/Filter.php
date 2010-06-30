<?php
/**
 * PHPFrame/Filter/Filter.php
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
 * Abstract filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_Filter
{
    /**
     * Reflection object of this class used for checking extending classes.
     *
     * @var ReflectionClass
     */
    private $_reflection_obj;
    /**
     * Options array
     *
     * @var array
     */
    private $_options = array();
    /**
     * Messages array
     *
     * @var array
     */
    private $_messages = array();

    /**
     * Constructor
     *
     * @param array $options An associative array with filter options. To see
     *                       the available options call
     *                       PHPFrame_Filter::getOptions().
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->_reflection_obj = new ReflectionClass($this);

        if (!is_null($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Get option value
     *
     * @param string $key The option name or key
     *
     * @return mixed The option value
     * @since  1.0
     */
    final public function getOption($key)
    {
        $key = trim((string) $key);

        if (!isset($this->_options[$key])) {
            $msg = "Option '".$key."' not recognised.";
            throw new InvalidArgumentException($msg);
        }

        return $this->_options[$key];
    }

    /**
     * Set an option value
     *
     * @param string $key   The option name or key
     * @param mixed  $value The option value
     *
     * @return void
     * @since  1.0
     */
    final public function setOption($key, $value)
    {
        $key = trim((string) $key);

        if (!array_key_exists($key, $this->getOptions())) {
            $msg = "Option '".$key."' not recognised.";
            throw new InvalidArgumentException($msg);
        }

        // Guess setter method name based on key
        $setter_name  = str_replace("_", " ", $key);
        $setter_name .= "set".str_replace(" ", "", ucwords($setter_name));

        // If a setter has been defined for the option we use that, otherwise
        // we directly set the key in the internal array
        if ($this->_reflection_obj->hasMethod($setter_name)) {
            $this->$setter_name($value);
        } else {
            $this->_options[$key] = $value;
        }
    }

    /**
     * Set options array
     *
     * @param array $options An associative array with the filter options
     *
     * @return void
     * @since  1.0
     */
    final public function setOptions(array $options)
    {
        $array_obj = new PHPFrame_Array($options);
        if (!$array_obj->isAssoc()) {
            $msg = get_class($this)."::setOptions() expected \$options ";
            $msg = "argument to be an associative array.";
            throw new InvalidArgumentException($msg);
        }

        foreach ($options as $key=>$value) {
            if (array_key_exists($key, $this->getOptions())) {
                $this->setOption($key, $value);
            }
        }
    }

    /**
     * Get options array
     *
     * @return array
     * @since  1.0
     */
    final public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get mesages array
     *
     * @return array
     * @since  1.0
     */
    final public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Process given value with filter
     *
     * @param mixed $value The value to process
     *
     * @return mixed Either the filtered value or FALSE on failure
     * @since  1.0
     */
    abstract public function process($value);


    /**
     * Register an filter a option. This method is designed to be used by
     * implementing classes and register their specific options before
     * constructing their parent class.
     *
     * @param string $key           The option key
     * @param mixed  $default_value [Optional] A default value for the option
     *
     * @return void
     * @since  1.0
     */
    final protected function registerOption($key, $default_value=null)
    {
        $this->_options[(string) $key] = $default_value;
    }

    /**
     * Fail filtering
     *
     * @param string $str             The failure message.
     * @param string $exception_class [Optional]
     *
     * @return void
     * @since  1.0
     */
    final protected function fail($str, $exception_class=null)
    {
        if (!is_string($str)) {
            $msg  = get_class($this)."::fail() expected argument \$str ";
            $msg .= "to be of type 'string' and got '".gettype($str)."'.";
            throw new InvalidArgumentException($msg);
        }

        $this->_messages[] = array($str, $exception_class);
    }
}
