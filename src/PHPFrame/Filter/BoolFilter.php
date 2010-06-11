<?php
/**
 * PHPFrame/Filter/BoolFilter.php
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
 * Boolean Filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_BoolFilter extends PHPFrame_Filter
{
    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array with the filter
     *                                  options. The FloatFilter supports the
     *                                  following options:
     *
     *                                  - null_on_failure (bool). Default value
     *                                    is TRUE.
     *                                  - strict (bool). Default value is FALSE.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->registerOption("null_on_failure", true);
        $this->registerOption("strict", false);

        parent::__construct($options);
    }

    /**
     * Set null_on_failure option. If set to TRUE PHPFrame_BoolFilter:: process()
     * will return NULL instead of FALSE on failure.
     *
     * @param bool $bool Boolean value indicating whether we want to switch on
     *                   or off the null_on_failure option.
     *
     * @return void
     * @since  1.0
     */
    public function setNullOnFailure($bool)
    {
        $this->setOption("null_on_failure", (bool) $bool);
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
     * Returns TRUE for bool(TRUE), int(1), string("1"), string("true"),
     * string("on") and string("yes"). Returns FALSE otherwise.
     *
     * If 'null_on_failure' option is set, FALSE is returned only for
     * bool(FALSE), int(0), string("0"), string("false"), string("off"),
     * string("no"), and string(""), and NULL is returned for all non-boolean
     * values.
     *
     * @param bool|int|string $value The value to process
     *
     * @return mixed See method description
     * @since  1.0
     */
    public function process($value)
    {
        $null_on_failure = $this->getOption("null_on_failure");

        // Check primitive type if in strict mode
        if ($this->getOption("strict") && !is_bool($value)) {
            $msg  = "Value is not of type bool and ".get_class($this)." is ";
            $msg .= "set to strict mode.";
            $this->fail($msg, "InvalidArgumentException");
            return $null_on_failure ? null : false;
        }

        if (is_float($value)
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
            $msg .= "only be of type 'bool', 'int' or 'string' and value ";
            $msg .= "'".$value_as_string."' of type '".gettype($value);
            $msg .= "' was passed.";
            $this->fail($msg, "InvalidArgumentException");
            return $null_on_failure ? null : false;
        }

        if ($value == "") {
            $value = "0";
        } elseif (is_int($value)) {
            $value = (string) $value;
        }

        // Delegate to filter_var function
        if ($null_on_failure === false) {
            $filtered_value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } else {
            $filtered_value = filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        if (($filtered_value === false && !$null_on_failure)
            || (is_null($filtered_value) && $null_on_failure)
        ) {
            $msg  = "Failed to validate value '".$value."' of type ";
            $msg .= gettype($value)." with filter ".get_class($this).".";
            $this->fail($msg);
        }

        return $filtered_value;
    }
}
