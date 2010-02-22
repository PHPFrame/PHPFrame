<?php
/**
 * PHPFrame/Documentor/ParamDoc.php
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
 * Parameter Documentor Class
 * 
 * @category PHPFrame
 * @package  Documentor
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_ParamDoc
{
    private $_array = array("name"=>null, "type"=>null, "description"=>null);
    
    /**
     * Constructor.
     * 
     * @param ReflectionParameter $reflection_param An object instance of 
     *                                              ReflectionParameter.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(ReflectionParameter $reflection_param)
    {
        $this->_array["name"] = $reflection_param->getName();
        
        $declaring_func = $reflection_param->getDeclaringFunction();
        $doc_comment    = (string) $declaring_func->getDocComment();
        
        $this->_parseDocComment($doc_comment);
    }
    
    /**
     * Convert object to string.
     * 
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";
        
        if ($this->getType()) {
            $str .= $this->getType()." ";
        }
        
        $str .= "$".$this->getName();
        
        if ($this->getDescription()) {
            $str .= " ".$this->getDescription();
        }
        
        return $str;
    }
    
    /**
     * Implementation of the IteratorAggregate interface.
     * 
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_array);
    }
    
    /**
     * Get parameter name.
     * 
     * @return string
     * @since  1.0
     */
    public function getName()
    {
        return $this->_array["name"];
    }
    
    /**
     * Get parameter type.
     * 
     * @return string
     * @since  1.0
     */
    public function getType()
    {
        return $this->_array["type"];
    }
    
    /**
     * Get parameter description.
     * 
     * @return string
     * @since  1.0
     */
    public function getDescription()
    {
        return $this->_array["description"];
    }
    
    /**
     * Parse docblock comment.
     * 
     * @param string $str The docblock text to parse.
     * 
     * @return void
     * @since  1.0
     * @todo   Have to parse docblock using tokenizer extension.
     */
    private function _parseDocComment($str)
    {
        if (!is_string($str)) {
            $msg = "Argument \$str must be of type string.";
            throw new InvalidArgumentException($msg);
        }
        
        $array = explode("\n", $str);
        
        foreach ($array as $line) {
            $line = trim($line, "/*\n\t ");
            
            if (empty($line)) {
                continue;
            }
            
            $pattern = '/^@param\s+([\w|]+)\s+\$'.$this->getName().'(\s+)?(.*)?/';
            if (preg_match($pattern, $line, $matches)) {
                $this->_array["type"] = $matches[1];
                $this->_array["description"] = $matches[3];
            }
        }
    }
}
