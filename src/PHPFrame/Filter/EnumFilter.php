<?php
/**
 * PHPFrame/Filter/EnumFilter.php
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
 * Enum Filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_EnumFilter extends PHPFrame_Filter
{
    /**
     * Constructor.
     *
     * @param array $options [Optional] An associative array with the filter
     *                       options. The FloatFilter supports the following
     *                       options:
     *
     *                       - enums (array)
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->registerOption('enums', array());
        parent::__construct($options);
    }

    /**
     * Processes the given enum value against the allowed enums.
     *
     * @param mixed $value The value to filter.
     *
     * @return mixed Either the filtered value or FALSE on failure
     * @see src/PHPFrame/Filter/PHPFrame_Filter#process($value)
     * @since  1.0
     */
    public function process($value)
    {
        $enums = $this->getOption('enums');
        $found = false;
        foreach ($enums as $enum) {
            if ($value == $enum) {
                $found = true;
            }
        }
        if (!$found) {
            $msg  = "Argument \$value in ".get_class($this)."::process() is ";
            $msg .= "not one of the stored enums.";
            $this->fail($msg, 'InvalidArgumentException');
            return false;
        } else {
            return $value;
        }
    }

    /**
     * Set enums.
     *
     * @param array $enums An array containing the values allowed by the filter.
     *
     * @return void
     * @since  1.0
     */
    public function setEnums(array $enums)
    {
        $this->setOption('enums', $enums);
    }
}