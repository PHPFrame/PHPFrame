<?php
/**
 * PHPFrame/Document/PlainRenderer.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Plain text renderer class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_IRenderer
 * @since    1.0
 */
class PHPFrame_PlainRenderer implements PHPFrame_IRenderer
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
            
        } elseif ($value instanceof Traversable) {
            return $this->_renderArray(iterator_to_array($value));
            
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
        
        return "\"".strip_tags(trim((string) $value))."\"";
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
        
        return $this->_renderArray(array($key=>$value));
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
