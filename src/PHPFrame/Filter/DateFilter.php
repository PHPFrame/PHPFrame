<?php
/**
 * PHPFrame/Filter/DateFilter.php
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
 * Date Filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_DateFilter extends PHPFrame_Filter
{
    const FORMAT_DATE     = "DATE";
    const FORMAT_TIME     = "TIME";
    const FORMAT_DATETIME = "DATETIME";

    /**
     * Constructor.
     *
     * @param array $options [Optional] An associative array with the filter
     *                       options. The DateFilter supports the following
     *                       options:
     *
     *                       - format (string). Allowed values are: "DATE",
     *                         "TIME" or "DATETIME".
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->registerOption('format', self::FORMAT_DATE);
        parent::__construct($options);
    }

    /**
     * Processes the given date value against the format.
     *
     * @param string $value The value to filter.
     *
     * @return mixed Either the filtered value or FALSE on failure
     * @see src/PHPFrame/Filter/PHPFrame_Filter#process($value)
     * @since  1.0
     */
    public function process($value)
    {
        if (!is_string($value)) {
            $msg  = "Value passed to ".get_class($this)."::".__FUNCTION__."()";
            $msg .= " must be of type string.";
            $this->fail($msg, 'InvalidArgumentException');
            return false;
        }

        switch($this->getOption('format')) {
        case self::FORMAT_DATE :
            $pattern = "/^\d{4}-\d{2}-\d{2}$/";
            break;

        case self::FORMAT_DATETIME :
            $pattern = "/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/";
            break;

        case self::FORMAT_TIME :
            $pattern = "/^\d{2}:\d{2}:\d{2}$/";
            break;
        }

        if (!preg_match($pattern, $value)) {
            $msg  = "Could not validate date format '";
            $msg .= $this->getOption('format')."' for value '".$value."'.";
            $this->fail($msg, 'InvalidArgumentException');
            return false;
        }

        return $value;
    }

    /**
     * Set format.
     *
     * @param array $format The date format tp use for validation. Allowed
     *                      values are: "DATE", "TIME" or "DATETIME".
     *
     * @return void
     * @since  1.0
     */
    public function setFormat($format)
    {
        $formats = array(
            self::FORMAT_DATE,
            self::FORMAT_DATETIME,
            self::FORMAT_TIME
        );

        if (!in_array($format, $formats)) {
            $msg  = "Wrong date format ('".$format."') passed to ";
            $msg .= get_class($this)."::".__FUNCTION__."(). ";
            $msg .= "Allowed values are : ".implode(", ", $formats).".";
            throw new InvalidArgumentException($msg);
        }

        $this->setOption("format", $format);
    }
}
