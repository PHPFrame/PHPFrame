<?php
/**
 * PHPFrame/Base/Object.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Base
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * The base "Object" class provides a standard object with added functionality
 * for stricter type checking and some other useful methods.
 *
 * @category PHPFrame
 * @package  Base
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Object
{
    const SCALAR_TYPE_BOOL     = "bool";
    const SCALAR_TYPE_INT      = "int";
    const SCALAR_TYPE_FLOAT    = "float";
    const SCALAR_TYPE_STRING   = "string";
    const SCALAR_TYPE_RESOURCE = "resource";
    const SCALAR_TYPE_NULL     = "null";

    private $_reflector = null;

    /**
     * The __get() magic method is automatically invoked when an undefined
     * property is accessed.
     *
     * @param string $prop_name The property name.
     *
     * @return void
     * @throws LogicException
     * @since  1.0
     */
    public function __get($prop_name)
    {
        $msg  = "Property '".$prop_name."' does not exist in class '";
        $msg .= get_class($this)."'.";
        throw new LogicException($msg);
    }

    /**
     * The __set() magic method is automatically invoked when an undefined
     * property is set.
     *
     * @param string $prop_name  The property name.
     * @param mixed  $prop_value The property value.
     *
     * @return void
     * @throws LogicException
     * @since  1.0
     */
    public function __set($prop_name, $prop_value)
    {
        $msg  = "Property '".$prop_name."' does not exist in class '";
        $msg .= get_class($this)."'.";
        throw new LogicException($msg);
    }

    /**
     * Get instance of ReflectionClass for this object.
     *
     * @return ReflectionClass
     * @since  1.0
     */
    public function getReflector()
    {
        if (is_null($this->_reflector)) {
            $this->_reflector = new ReflectionClass($this);
        }

        return $this->_reflector;
    }

    /**
     * Enforce scalar types.
     *
     * @return void
     * @throws LogicException|InvalidArgumentException
     * @since  1.0
     */
    protected function enforceArgumentTypes()
    {
        $call     = debug_backtrace();
        $function = $call[1]["function"];
        $class    = $call[1]["class"];
        $args     = $call[1]["args"];

        $reflection_method = $this->getReflector()->getMethod($function);
        $docblock          = $reflection_method->getDocComment();
        $params            = array();
        $i                 = 0;

        foreach ($reflection_method->getParameters() as $param) {
            $pattern = '/@param\s+(\w+)\s+\$'.$param->getName().'/';
            preg_match($pattern, $docblock, $matches);

            if (!isset($matches[1])) {
                $msg  = "No type defined for argument ".$param->getName();
                $msg .= " in docblock comment.";
                throw new LogicException($msg);
            }

            if (in_array($matches[1], $this->_getScalarTypes())
                && !call_user_func("is_".$matches[1], $args[$i])
            ) {
                $msg  = "Argument '".$param->getName()."' in ".$class."::";
                $msg .= $function."() must be of type '".$matches[1]."' and ";
                $msg .= "value of type '".gettype($args[$i])."' was passed.";
                throw new InvalidArgumentException($msg);
            }

            $i++;
        }
    }

    /**
     * Check whether a given value is of the type specified in docblock
     * comment.
     *
     * @param mixed $value The value for which we want to enforce return type.
     *
     * @return void
     * @throws LogicException|RuntimeException
     * @since  1.0
     */
    protected function enforceReturnType($value)
    {
        $call              = debug_backtrace();
        $function          = $call[1]["function"];
        $class             = $call[1]["class"];
        $reflection_method = $this->getReflector()->getMethod($function);
        $docblock          = $reflection_method->getDocComment();

        preg_match('/@return\s+(\w+)\s/', $docblock, $matches);

        if (!isset($matches[1])) {
            $msg  = "No return type defined for ".$class."::".$function."()";
            $msg .= " in docblock comment.";
            throw new LogicException($msg);
        }

        $type        = $matches[1];
        $passed_type = gettype($value);

        if ($type == "bool") {
            $type = "boolean";
        }

        if ($passed_type != $type) {
            $msg  = "Return type not valid. ".get_class($this)."::".$function;
            $msg .= "() must return type '".$matches[1]."' and checked ";
            $msg .= "return value was of type '".$passed_type."'.";
            throw new RuntimeException($msg);
        }
    }

    /**
     * Get array with scalar types as defined in class constants.
     *
     * @return array
     * @since  1.0
     */
    private function _getScalarTypes()
    {
        return array(
            self::SCALAR_TYPE_BOOL,
            self::SCALAR_TYPE_FLOAT,
            self::SCALAR_TYPE_INT,
            self::SCALAR_TYPE_NULL,
            self::SCALAR_TYPE_RESOURCE,
            self::SCALAR_TYPE_STRING
        );
    }
}
