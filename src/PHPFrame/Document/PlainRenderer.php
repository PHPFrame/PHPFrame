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
class PHPFrame_PlainRenderer extends PHPFrame_Renderer
{
    private $_indent = "";
    private $_nl = "\n";

    /**
     * Render a given value.
     *
     * @param mixed $value The value we want to render.
     *
     * @return void
     * @since  1.0
     */
    public function render($value)
    {
        if (is_null($value)) {
            return;

        } elseif (is_array($value)) {
            return $this->_renderArray($value);

        } elseif ($value instanceof Exception) {
            return $this->_renderException($value);

        } elseif ($value instanceof Traversable) {
            return $this->_renderTraversable($value);

        } elseif (is_object($value)) {
            return $this->_renderObject($value);

        } elseif (is_bool($value)) {
            if ($value) {
                $value = 1;
            } else {
                return;
            }
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);
        if (is_int($int)) {
            return (string) $int;
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);
        if (is_float($float)) {
            return (string) $float;
        }

        return trim((string) $value);
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
        return print_r($array, true);
    }

    /**
     * Render traversable objects.
     *
     * @param Traversable $obj The object to render.
     *
     * @return string
     * @since  1.0
     */
    private function _renderTraversable(Traversable $obj)
    {
        $str = $this->_renderArray(iterator_to_array($obj));

        return preg_replace("/^Array/", get_class($obj), $str);
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
        $str =  $this->_renderArray(get_object_vars($obj));

        return preg_replace("/^Array/", get_class($obj), $str);
    }

    /**
     * Render exception object.
     *
     * @param Exception $e Instance of Exception.
     *
     * @return string
     * @since  1.0
     */
    private function _renderException(Exception $e)
    {
        $str  = "Uncaught ".get_class($e).": ";
        $str .= $e->getMessage()."\n";
        $str .= "File: ".$e->getFile()."\n";
        $str .= "Line: ".$e->getLine()."\n";
        $str .= "Code: ".$e->getCode()."\n";
        $str .= $e->getTraceAsString();

        return $str;
    }
}
