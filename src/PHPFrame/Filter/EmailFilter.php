<?php
/**
 * PHPFrame/Filter/EmailFilter.php
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
 * Email Filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_EmailFilter extends PHPFrame_StringFilter
{
    /**
     * Constructor
     *
     * Optionas are inherited from parent PHPFrame_StringFilter
     *
     * @param array $options [Optional] An associative array with the filter
     *                                  options. The FloatFilter supports the
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
        parent::__construct($options);
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
        $value = parent::process($value);

        // Delegate to filter_var function
        $filtered_value = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($filtered_value === false) {
            $msg  = "Failed to validate value ".gettype($value)."('".$value;
            $msg .= "') with filter ".get_class($this).".";
            $this->fail($msg);
        }

        return $filtered_value;
    }
}
