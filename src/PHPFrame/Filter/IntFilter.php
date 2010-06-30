<?php
/**
 * PHPFrame/Filter/IntFilter.php
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
 * Integer Filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_IntFilter extends PHPFrame_Filter
{
    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array with the filter
     *                                  options. The FloatFilter supports the
     *                                  following options:
     *
     *                                  - min_range (int). Default value
     *                                    is -2147483648. (4 byte signed int)
     *                                  - max_range (int). Default value is
     *                                    2147483648. (4 byte signed int)
     *                                  - allow_octal (bool). Default value is
     *                                    FALSE
     *                                  - allow_hex (bool). Default value is
     *                                    FALSE
     *                                  - strict (bool)
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->registerOption("min_range", -2147483648);
        $this->registerOption("max_range", 2147483647);
        $this->registerOption("allow_octal", false);
        $this->registerOption("allow_hex", false);
        $this->registerOption("strict", false);

        parent::__construct($options);
    }

    /**
     * Set minimum allowed range
     *
     * @param int $int The minimum allowed value for the filtered integer
     *
     * @return void
     * @since  1.0
     */
    public function setMinRange($int)
    {
        $this->setOption("min_range", (int) $int);
    }

    /**
     * Set maximum allowed range
     *
     * @param int $int The maximum allowed value for the filtered integer
     *
     * @return void
     * @since  1.0
     */
    public function setMaxRange($int)
    {
        $this->setOption("max_range", (int) $int);
    }

    /**
     * Set whether or not octal notation integers are allowed by filter
     *
     * @param bool $bool Boolean to indicate whether or not octals are allowed
     *
     * @return void
     * @since  1.0
     */
    public function setAllowOctal($bool)
    {
        $this->setOption("allow_octal", (bool) $bool);
    }

    /**
     * Set whether or not hexadecimal notation integers are allowed by filter
     *
     * @param bool $bool Boolean to indicate whether or not hex are allowed
     *
     * @return void
     * @since  1.0
     */
    public function setAllowHex($bool)
    {
        $this->setOption("allow_hex", (bool) $bool);
    }

    /**
     * Set strict option
     *
     * @param bool $bool Boolean indicating whether strict mode is on or off
     *
     * @return void
     * @since  1.0
     */
    public function setStrict($bool)
    {
        $this->setOption("strict", (bool) $bool);
    }

    /**
     * Process given value with filter
     *
     * Note that when using 'allow_octal' and 'allow_hex' options strings that
     * represent this notations will be converted to integers and validation
     * will succeed. For example, if validating string "0x00000001" with
     * 'allow_hex' option process() will return TRUE.
     *
     * @param int|string $value The value to process
     *
     * @return mixed See method description
     * @since  1.0
     */
    public function process($value)
    {
        // Check primitive type if in strict mode
        if ($this->getOption("strict") && !is_int($value)) {
            $msg  = "Value is not of type int and ".get_class($this)." is ";
            $msg .= "set to strict mode.";
            $this->fail($msg, "InvalidArgumentException");
            return false;
        }

        if (is_bool($value)
            || is_float($value)
            || is_array($value)
            || is_object($value)
            || is_resource($value)
        ) {
            if (is_object($value) || is_resource($value)) {
                $value_as_string = gettype($value);
            } else {
                $value_as_string = (string) $value;
            }

            $msg  = "Argument \$value in ".get_class($this)."::process() can ";
            $msg .= "only be of type 'int' or 'string' and value ";
            $msg .= "'".$value_as_string."' of type '".gettype($value);
            $msg .= "' was passed.";
            $this->fail($msg, "InvalidArgumentException");
            return false;
        }

        // Delegate to filter_var function
        // First we build options array for filter_var()
        $options = array();
        $options["min_range"] = $this->getOption("min_range");
        $options["max_range"] = $this->getOption("max_range");

        // Set flags for filter_var()
        $flags = null;
        if ($this->getOption("allow_octal") && $this->getOption("allow_hex")) {
            $flags = FILTER_FLAG_ALLOW_OCTAL|FILTER_FLAG_ALLOW_HEX;
        }
        if ($this->getOption("allow_octal")) {
            $flags = FILTER_FLAG_ALLOW_OCTAL;
        }
        if ($this->getOption("allow_hex")) {
            $flags = FILTER_FLAG_ALLOW_HEX;
        }

        // Pack options and flags into a single array
        //if (!is_null($flags)) {
            $options = array("options"=>$options, "flags"=>$flags);
        //}

        $filtered_value = filter_var($value, FILTER_VALIDATE_INT, $options);
        if ($filtered_value === false) {
            $msg  = "Failed to validate value '".$value."' of type ";
            $msg .= gettype($value)." with filter ".get_class($this).".";
            $this->fail($msg);
        }

        return $filtered_value;
    }
}
