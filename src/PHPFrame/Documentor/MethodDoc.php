<?php
/**
 * PHPFrame/Documentor/MethodDoc.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Documentor
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @ignore
 */

/**
 * Method Documentor Class
 *
 * @category PHPFrame
 * @package  Documentor
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_MethodDoc extends ReflectionMethod
{
    /**
     * Convert object to string.
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = $this->getName()."(";

        foreach ($this->getParameters() as $param) {
            $param_str = "\$".$param->getName();

            if ($param->isOptional()) {
                $param_str = "[".$param_str."]";
            }

            $str .= $param_str;
        }

        $str .= ")";

        return $str;
    }
}
