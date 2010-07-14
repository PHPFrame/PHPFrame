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
    private $_description = "";
    private $_params = array();
    private $_return;
    private $_since;

    /**
     * Constructor
     *
     * @param string $class_name  The class name.
     * @param string $method_name The method name.
     *
     * @return void
     * @since  1.1
     */
    public function __construct($class_name, $method_name)
    {
        parent::__construct($class_name, $method_name);

        if (!preg_match("/\/\*\*(.+)\*\//s", $this->getDocComment(), $matches)) {
            return;
        }

        foreach (explode("\n", $matches[1]) as $line) {
            $line = trim($line, " *");
            if ($line) {
                if (preg_match("/^@since\s+([\d.]+)/", $line, $since_matches)) {
                    $this->_since = $since_matches[1];
                } elseif (preg_match("/^@return\s+([a-zA-Z0-9]+)\s*([\w]*)/", $line, $return_matches)) {
                    $this->_return = $return_matches[1];
                } elseif (preg_match("/^@param\s+(\w+)\s+[$](\w+)\s+(.+)/", $line, $param_matches)) {
                    $this->_params[$param_matches[2]] = array(
                        "type" => $param_matches[1],
                        "description" => $param_matches[3]
                    );
                } else {
                    if (count($this->_params) === 0) {
                        $this->_description .= $line;
                    } else {
                        $last_param_key = end(array_keys($this->_params));
                        $this->_params[$last_param_key]["description"] .= " ".$line;
                    }
                }
            }
        }
    }

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

    /**
     * Get method description.
     *
     * @return string
     * @since  1.1
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Get method return type.
     *
     * @return string
     * @since  1.1
     */
    public function getReturnType()
    {
        return $this->_return;
    }

    /**
     * Get method since tag.
     *
     * @return string
     * @since  1.1
     */
    public function getSince()
    {
        return $this->_since;
    }

    /**
     * Get method signature.
     *
     * @return string
     * @since  1.1
     */
    public function getSignature()
    {
        $str = $this->getReturnType()." ".$this->getName()."(";

        $i=0;
        foreach ($this->getParameters() as $param) {
            if ($i>0) {
                $str .= ", ";
            }

            $param_array = get_object_vars($param);
            $param_str = "";

            if (array_key_exists("type", $param_array)) {
                $param_str .= $param_array["type"]." ";
            }

            $param_str .= $param_array["name"]."";

            if ($param->isOptional()) {
                $param_str = "[ ".$param_str." ]";
            }

            $str .= $param_str;

            $i++;
        }

        $str .= ")";

        return $str;
    }

    /**
     * Get method parameters.
     *
     * @return array containing ReflectionParameter objects.
     * @since  1.1
     */
    public function getParameters()
    {
        $params = array();

        foreach (parent::getParameters() as $param) {
            if (array_key_exists($param->getName(), $this->_params)) {
                $param->type = $this->_params[$param->getName()]["type"];
                $param->description = $this->_params[$param->getName()]["description"];
            }

            $params[] = $param;
        }

        return $params;
    }
}
