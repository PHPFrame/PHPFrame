<?php
/**
 * PHPFrame/Filter/FloatFilter.php
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
 * Float Filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_FloatFilter extends PHPFrame_Filter
{
    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array with the filter
     *                                  options. The FloatFilter supports the
     *                                  following options:
     *
     *                                  - decimal (string) The character used to
     *                                    represent the decimal point. Default
     *                                    value is dot (.).
     *                                  - allow_thousand (bool). Whether or not
     *                                    to allow commas as thousands
     *                                    separator. Default value is FALSE
     *                                  - strict (bool) Default value is FALSE
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->registerOption("decimal", ".");
        $this->registerOption("allow_thousand", false);
        $this->registerOption("strict", false);

        parent::__construct($options);
    }

    /**
     * Set the character used to represent the decimal point. Defauly value is
     * dot (.)
     *
     * If passed a string of more than one character, only the first character
     * of the given string will be used.
     *
     * @param string $str Decimal point character
     *
     * @return void
     * @since  1.0
     */
    public function setDecimal($str)
    {
        $this->setOption("decimal", substr((string) $str, 0, 1));
    }

    /**
     * Set whether or not commas are allowed as thousands separator
     *
     * @param bool $bool Boolean to indicate option on or off
     *
     * @return void
     * @since  1.0
     */
    public function setAllowThousand($bool)
    {
        $this->setOption("allow_thousand", (bool) $bool);
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
     * If boolean TRUE is passed and strict mode is off the value will be
     * automatically converted to float(1).
     *
     * @param int|float|string $value The value to process
     *
     * @return mixed Either the filtered value or FALSE on failure
     * @since  1.0
     * @see src/PHPFrame/Filter/PHPFrame_Filter#process($value)
     */
    public function process($value)
    {
        // Check primitive type if in strict mode
        if ($this->getOption("strict") && !is_float($value)) {
            $msg  = "Value is not of type float and ".get_class($this)." is ";
            $msg .= "set to strict mode.";
            $this->fail($msg, "InvalidArgumentException");
            return false;
        }

        if (is_bool($value)
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
        $options = array("decimal" => $this->getOption("decimal"));
        // Set flags for filter_var()
        $flags = null;
        if ($this->getOption("allow_thousand")) {
            $flags = FILTER_FLAG_ALLOW_THOUSAND;
        }
        // Pack options and flags into a single array
        $options = array("options"=>$options, "flags"=>$flags);

        $filtered_value = filter_var($value, FILTER_VALIDATE_FLOAT, $options);
        if ($filtered_value === false) {
            $msg  = "Failed to validate value '".gettype($value)."(".$value;
            $msg .= ")' with filter ".get_class($this).".";
            $this->fail($msg);
        }

        return $filtered_value;
    }
}
