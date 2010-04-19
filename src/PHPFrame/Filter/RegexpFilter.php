<?php
/**
 * PHPFrame/Filter/RegexpFilter.php
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
 * Regular expression filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_RegexpFilter extends PHPFrame_StringFilter
{
    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array with the filter
     *                                  options. The RegexpFilter supports the
     *                                  following options:
     *
     *                                  - regexp
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
        $this->registerOption("regexp", "/^.*/");

        parent::__construct($options);
    }

    /**
     * Set regular expression for filtering
     *
     * @param string $str The regular expression
     *
     * @return void
     * @since  1.0
     */
    public function setRegexp($str)
    {
        $this->setOption("regexp", (string) $str);
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
        // Process with parent first
        $parent_value = parent::process($value);
        if ($parent_value === false) {
            return false;
        }

        // Delegate to filter_var function
        $options        = array("regexp"=>$this->getOption("regexp"));
        $options        = array("options"=>$options);
        $filtered_value = filter_var(
            $parent_value,
            FILTER_VALIDATE_REGEXP,
            $options
        );

        if ($filtered_value === false) {
            $msg  = "Failed to validate value '".gettype($value)."(".$value;
            $msg .= ")' with filter ".get_class($this)." using reguar ";
            $msg .= "expression '".$this->getOption("regexp")."'.";
            $this->fail($msg);
        }

        return $filtered_value;
    }
}
