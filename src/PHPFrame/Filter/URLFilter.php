<?php
/**
 * PHPFrame/Filter/URLFilter.php
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
 * URL Filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_URLFilter extends PHPFrame_Filter
{
    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array with the filter
     *                                  options. The RegexpFilter supports the
     *                                  following options:
     *
     *                                  - path_required (bool)
     *                                  - query_required (int)
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->registerOption("path_required", false);
        $this->registerOption("query_required", false);

        parent::__construct($options);
    }

    /**
     * Set 'path_required' option
     *
     * @param bool $bool Boolean indicating whether option is on or off
     *
     * @return void
     * @since  1.0
     */
    public function setPathRequired($bool)
    {
        $this->setOption("path_required", (bool) $bool);
    }

    /**
     * Set 'query_required' option
     *
     * @param bool $bool Boolean indicating whether option is on or off
     *
     * @return void
     * @since  1.0
     */
    public function setQueryRequired($bool)
    {
        $this->setOption("query_required", (bool) $bool);
    }

    /**
     * Process the given value using the filter
     *
     * @param string $value The value to process
     *
     * @return mixed Either the filtered value or FALSE on failure
     * @see    src/PHPFrame/Filter/PHPFrame_Filter#process($value)
     * @since  1.0
     */
    public function process($value)
    {
        // Always check primitive type is string
        if (!is_string($value)) {
            if (is_object($value) || is_resource($value)) {
                $value_as_string = gettype($value);
            } else {
                $value_as_string = (string) $value;
            }

            $msg  = "Argument \$value in ".get_class($this)."::process() must ";
            $msg .= "be of type string and got value '".$value_as_string."' of ";
            $msg .= "type ".gettype($value).".";
            $this->fail($msg, "InvalidArgumentException");
            return false;
        }

        // Delegate to filter_var function
        // Set flags for filter_var()
        $flags = null;
        if ($this->getOption("path_required")) {
            $flags = FILTER_FLAG_PATH_REQUIRED;
        }
        if ($this->getOption("query_required")) {
            $flags = $flags|FILTER_FLAG_QUERY_REQUIRED;
        }

        $filtered_value = filter_var($value, FILTER_VALIDATE_URL, $flags);
        if ($filtered_value === false) {
            $msg  = "Failed to validate value '".gettype($value)."(".$value;
            $msg .= ")' with filter ".get_class($this).".";
            $this->fail($msg);
        }

        return $filtered_value;
    }
}
