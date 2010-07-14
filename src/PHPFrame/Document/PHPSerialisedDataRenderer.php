<?php
/**
 * PHPFrame/Document/PHPSerialisedDataRenderer.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Document
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * PHP Serialised Data renderer class
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Renderer
 * @since    1.0
 */
class PHPFrame_PHPSerialisedDataRenderer extends PHPFrame_Renderer
{
    private $_base64 = false;

    public function __construct($base64=true)
    {
        $this->base64($base64);
    }

    /**
     * Get/set flag indicating whether renderer should encode serialised string
     * in base64.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function base64($bool=null)
    {
        if (!is_null($bool)) {
            $this->_base64 = (bool) $bool;
        }

        return $this->_base64;
    }

    /**
     * Render a given value.
     *
     * @param mixed $value The value we want to render.
     *
     * @return string|null
     * @since  1.0
     */
    public function render($value)
    {
        if ($value instanceof Exception) {
            $value = $this->exceptionToArray($value);
        }

        $value = serialize($value);

        if ($this->base64()) {
            $value = base64_encode($value);
        }

        return $value;
    }
}
