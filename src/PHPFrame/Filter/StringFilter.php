<?php
/**
 * PHPFrame/Filter/StringFilter.php
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
 * String Filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_StringFilter extends PHPFrame_Filter
{
    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array with the filter
     *                                  options. The StringFilter supports the
     *                                  following options:
     *
     *                                  - min_length (int)
     *                                  - max_length (int)
     *                                  - truncate (bool)
     *                                  - strict (bool)
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->registerOption("min_length", 0);
        $this->registerOption("max_length", -1);
        $this->registerOption("truncate", false);
        $this->registerOption("strict", false);

        parent::__construct($options);
    }

    /**
     * Set minimum allowed length
     *
     * @param int $int The minimum number of characters
     *
     * @return void
     * @since  1.0
     */
    public function setMinLength($int)
    {
        $this->setOption("min_length", (int) $int);
    }

    /**
     * Set maximum allowed length
     *
     * @param int $int The number of maximum allowed characters
     *
     * @return void
     * @since  1.0
     */
    public function setMaxLength($int)
    {
        $this->setOption("max_length", (int) $int);
    }

    /**
     * Set truncate option
     *
     * @param bool $bool Boolean indicating whether truncate mode is on or off
     *
     * @return void
     * @since  1.0
     */
    public function setTruncate($bool)
    {
        $this->setOption("truncate", (bool) $bool);
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
     * Process the given value using the filter
     *
     * @param int|float|string $value The value to process
     *
     * @return mixed Either the filtered value or FALSE on failure
     * @see    src/PHPFrame/Filter/PHPFrame_Filter#process($value)
     * @since  1.0
     */
    public function process($value)
    {
        // Check primitive type if in strict mode
        if ($this->getOption("strict") && !is_string($value)) {
            $msg  = "Value is not of type string and ".get_class($this)." is ";
            $msg .= "set to strict mode.";
            $this->fail($msg);
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

        // Cast value to string (this applies when not in strict mode)
        $value = trim((string) $value);

        // Check minimum length
        if (strlen($value) < $this->getOption("min_length")) {
            $msg  = "Value is too short. Required minimum length is ";
            $msg .= $this->getOption("min_length")." and passed value ";
            $msg .= "('".$value."') is only ".strlen($value)." characters long.";
            $this->fail($msg, "LengthException");
            return false;
        }

        // Get max length option
        $max_length = $this->getOption("max_length");

        // Truncate if set to do so before we check max length
        if ($max_length > 0 && $this->getOption("truncate")) {
            $value = substr($value, 0, $max_length);
        }

        // Check maximum length
        if ($max_length > 0 && strlen($value) > $max_length) {
            $msg  = "Value is too long. Required maximum length is ";
            $msg .= $this->getOption("max_length")." and passed value ";
            $msg .= "('".$value."') is ".strlen($value)." characters long.";
            $this->fail($msg, "LengthException");
            return false;
        }

        // Delegate to filter_var function
        $filtered_value = filter_var($value, FILTER_DEFAULT);
        if ($filtered_value === false) {
            $msg  = "Failed to validate value '".gettype($value)."(".$value;
            $msg .= ")' with filter ".get_class($this).".";
            $this->fail($msg);
        }

        return $filtered_value;
    }
}
