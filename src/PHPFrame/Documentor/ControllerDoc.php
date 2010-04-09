<?php
/**
 * PHPFrame/Documentor/ControllerDoc.php
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
 * Controller Documentor Class
 *
 * @category PHPFrame
 * @package  Documentor
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_ControllerDoc extends PHPFrame_ClassDoc
{
    /**
     * Convert object to string.
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str     = "";
        $actions = $this->getActions();

        if (count($actions) > 0) {
            $str .= "Actions:";
            foreach ($actions as $action) {
                $str .= "\n".$action->getName();
                $str .= "(";
                $count = 0;
                foreach ($action->getParameters() as $param) {
                    if ($count > 0) {
                        $str .= ", ";
                    }

                    $str .= "$".$param->getName();

                    $count++;
                }
                $str .= ")";
            }
        }

        return $str;
    }

    /**
     * Get array containing controller actions as objects of type
     * PHPFrame_MethodDoc.
     *
     * @return array
     * @since  1.0
     */
    public function getActions()
    {
        $methods = $this->getMethods();
        $actions = array();

        if (count($methods) > 0) {
            foreach ($methods as $method) {
                $declaring_class = $method->getDeclaringClass()->getName();
                if ($method->getName() != "__construct"
                    && $method->isPublic()
                    && $declaring_class == $this->getName()
                ) {
                    $actions[] = $method;
                }
            }
        }

        return $actions;
    }
}
