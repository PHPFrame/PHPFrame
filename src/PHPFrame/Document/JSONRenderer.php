<?php
/**
 * PHPFrame/Document/PlainRenderer.php
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
 * Plain text renderer class
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Renderer
 * @since    1.0
 */
class PHPFrame_JSONRenderer extends PHPFrame_Renderer
{
    private $_indent = "";
    private $_nl = "\n";
    private $_use_php_json = true;
    private $_jsonp_callback;

    /**
     * Constructor.
     *
     * @param bool $use_php_json [Optional] Flag indicating whether renderer
     *                           should try to use php's JSON extension if
     *                           available. If the extension is not available
     *                           the renderer will fall back to its own JSON
     *                           rendering functions (still a bit wonky). The
     *                           default value is TRUE. For more info about the
     *                           JSON extension check:
     *                           http://www.php.net/manual/en/book.json.php
     *
     * @return void
     * @since  1.0
     */
    public function __construct($use_php_json=true, $jsonp_callback=null)
    {
        $this->usePhpJson($use_php_json);
        $this->jsonpCallback($jsonp_callback);
    }

    /**
     * Get/set flag indicating whether renderer should try to use PHP's JSON
     * extension if available.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function usePhpJson($bool=null)
    {
        if (!is_null($bool)) {
            $this->_use_php_json = (bool) $bool;
        }

        return $this->_use_php_json;
    }

    /**
     * Get/set flag indicating whether renderer should try to use PHP's JSON
     * extension if available.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.0
     */
    public function jsonpCallback($str=null)
    {
        if (!is_null($str)) {
            $this->_jsonp_callback = (string) $str;
        }

        return $this->_jsonp_callback;
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
        } elseif ($value instanceof PHPFrame_RESTfulObject) {
        	$value = $value->getRESTfulRepresentation();
        } elseif ($value instanceof PHPFrame_PersistentObject) {
            $value = $this->persistentObjectToArray($value);
        } elseif ($value instanceof PHPFrame_PersistentObjectCollection) {
            $value = $this->persistentObjectCollectionToArray($value);
        }

        if (function_exists("json_encode") && $this->usePhpJson()) {
            $str = json_encode($value);
        } else {
            $str = $this->_renderWithoutJsonExtension($value);
        }

        if ($this->jsonpCallback()) {
            $str = $this->jsonpCallback()."(".$str.");";
        }

        return $str;
    }

    /**
     * Render value as JSON string. This is used as fallback when php-json is
     * not installed.
     *
     * @param mixed $value The value we want to render.
     *
     * @return string|null
     * @since  1.0
     */
    private function _renderWithoutJsonExtension($value)
    {
        if (is_array($value)) {
            return $this->_renderArray($value);

        } elseif (is_object($value)) {
            return $this->_renderObject($value);

        } elseif (is_bool($value)) {
            if ($value) {
                return "true";
            } else {
                return "false";
            }
        } elseif (is_null($value)) {
            return "null";
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);
        if (is_float($float)) {
            return "\"".$float."\"";
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);
        if (is_int($int)) {
            return "\"".$int."\"";
        }

        return "\"".$this->_escape(trim((string) $value))."\"";
    }

    /**
     * Render array.
     *
     * @param array $array The array to render.
     *
     * @return string
     * @since  1.0
     */
    private function _renderArray(array $array)
    {
        if (count($array) == 0) {
            return "[]";
        }

        $array = new PHPFrame_Array($array);
        if ($array->isAssoc()) {
            return $this->_renderAssoc($array);
        }

        $str = "[".$this->_nl;
        $this->_increaseIndent();

        $i = 0;
        foreach ($array as $key=>$value) {
            if ($i > 0) {
                $str .= ",".$this->_nl;
            }

            $str .= $this->_indent.$this->render($value);
            $i++;
        }

        $this->_decreaseIndent();
        $str .= $this->_nl.$this->_indent."]";

        return $str;
    }

    /**
     * Render associative array.
     *
     * @param PHPFrame_Array $array Instance of PHPFrame_Array.
     *
     * @return string
     * @since  1.0
     */
    private function _renderAssoc(PHPFrame_Array $array)
    {
        $str = "{".$this->_nl;
        $this->_increaseIndent();

        $i = 0;
        foreach ($array as $key=>$value) {
            if ($i > 0) {
                $str .= ",".$this->_nl;
            }

            $str .= $this->_indent."\"".$key."\": ".$this->render($value);
            $i++;
        }

        $this->_decreaseIndent();
        $str .= $this->_nl.$this->_indent."}";

        return $str;
    }

    /**
     * Render object
     *
     * @param object $obj The object to render.
     *
     * @return string
     * @since  1.0
     */
    private function _renderObject($obj)
    {
        $key   = get_class($obj);
        $value = get_object_vars($obj);

        if ($key === "stdClass") {
            return $this->_renderAssoc(new PHPFrame_Array($value));
        }

        return $this->_renderArray(array($key=>$value));
    }

    /**
     * Escape string values.
     *
     * @param string $str The string to escape.
     *
     * @return string
     * @since  1.1
     */
    private function _escape($str)
    {
        return addcslashes($str, "\"\\/\n\r\t");
    }

    /**
     * Increase indentation.
     *
     * @return void
     * @since  1.0
     */
    private function _increaseIndent()
    {
        $this->_indent .= "    ";
    }

    /**
     * Decrease indentation.
     *
     * @return void
     * @since  1.0
     */
    private function _decreaseIndent()
    {
        $this->_indent = substr($this->_indent, 4);
    }
}
