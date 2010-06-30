<?php
/**
 * PHPFrame/Filter/IPFilter.php
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
 * IP Filter
 *
 * @category PHPFrame
 * @package  Filter
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_IPFilter extends PHPFrame_Filter
{
    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array with the filter
     *                                  options. The StringFilter supports the
     *                                  following options:
     *
     *                                  - ipv4
     *                                  - ipv6
     *                                  - no_priv_range
     *                                  - no_res_range
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->registerOption("ipv4", false);
        $this->registerOption("ipv6", false);
        $this->registerOption("no_priv_range", false);
        $this->registerOption("no_res_range", false);

        parent::__construct($options);
    }


    /**
     * Set ipv4 option
     *
     * @param bool $bool Boolean indicating whether option is on or off
     *
     * @return void
     * @since  1.0
     */
    public function setIpv4($bool)
    {
        $this->setOption("ipv4", (bool) $bool);
    }

    /**
     * Set ipv6 option
     *
     * @param bool $bool Boolean indicating whether option is on or off
     *
     * @return void
     * @since  1.0
     */
    public function setIpv6($bool)
    {
        $this->setOption("ipv6", (bool) $bool);
    }

    /**
     * Set no_priv_range option
     *
     * 10.0.0.0 - 10.255.255.255 Private IP addresses RFC 1918
     * 172.16.0.0 - 172.31.255.255 Private IP addresses RFC 1918
     * 192.168.0.0 - 192.168.255.255 Private IP addresses RFC 1918
     *
     * @param bool $bool Boolean indicating whether option is on or off
     *
     * @return void
     * @since  1.0
     */
    public function setNoPrivRange($bool)
    {
        $this->setOption("no_priv_range", (bool) $bool);
    }

    /**
     * Set no_res_range option
     *
     * 0.0.0.0 - 0.255.255.255 Zero Addresses RFC 1700
     * 169.254.0.0 - 169.254.255.255 Zeroconf/APIPA RFC 3330
     * 192.0.2.0 - 192.0.2.255 Documentation and Examples RFC 3330
     * 224.0.0.0 - 239.255.255.255 Multicast RFC 3171
     * 240.0.0.0 - 255.255.255.255 Reserved RFC 1166
     *
     * @param bool $bool Boolean indicating whether option is on or off
     *
     * @return void
     * @since  1.0
     */
    public function setNoResRange($bool)
    {
        $this->setOption("no_res_range", (bool) $bool);
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
        if ($this->getOption("ipv4")) {
            $flags = FILTER_FLAG_IPV4;
        }
        if ($this->getOption("ipv6")) {
            $flags = $flags|FILTER_FLAG_IPV6;
        }
        if ($this->getOption("no_priv_range")) {
            $flags = $flags|FILTER_FLAG_NO_PRIV_RANGE;
        }
        if ($this->getOption("no_res_range")) {
            $flags = $flags|FILTER_FLAG_NO_RES_RANGE;
        }

        $filtered_value = filter_var($value, FILTER_VALIDATE_IP, $flags);
        if ($filtered_value === false) {
            $msg  = "Failed to validate value '".gettype($value)."(".$value;
            $msg .= ")' with filter ".get_class($this).".";
            $this->fail($msg);
        }

        return $filtered_value;
    }
}
