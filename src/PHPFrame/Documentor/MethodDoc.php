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
class PHPFrame_MethodDoc implements IteratorAggregate
{
    private $_array = array(
        "name"            => null,
        "access"          => null,
        "declaring_class" => null,
        "params"          => array(),
        "return"          => array("type"=>null, "description"=>null),
        "since"           => null,
        "description"     => ""
    );
    private $_show_access = true;
    
    /**
     * Constructor.
     * 
     * @param ReflectionMethod $reflection_method An object instance of 
     *                                            ReflectionMethod.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(ReflectionMethod $reflection_method)
    {
        $this->_array["name"] = $reflection_method->getName();
        
        if ($reflection_method->isPublic()) {
            $this->_array["access"] = "public";
        } elseif ($reflection_method->isProtected()) {
            $this->_array["access"] = "protected";
        } elseif ($reflection_method->isPrivate()) {
            $this->_array["access"] = "private";
        }
        
        $declaring_class = $reflection_method->getDeclaringClass();
        $this->_array["declaring_class"] = $declaring_class->getName();
        
        foreach ($reflection_method->getParameters() as $reflection_param) {
            $param_doc = new PHPFrame_ParamDoc($reflection_param);
            $this->_array["params"][$param_doc->getName()] = $param_doc;
        }
        
        $doc_comment = (string) $reflection_method->getDocComment();
        
        
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
        $str  = $this->getAccess()." ";
        if ($this->getReturn()) {
            $str .= $this->getReturn()." ";
        }
        $str .= $this->getName()."( ";
        
        $count = 0;
        foreach ($this->getParams() as $param) {
            if ($count > 0) {
                $str .= ", ";
            }
            
            if ($param->getType()) {
                $str .= $param->getType()." ";
            }
            
            $str .= $param->getName();
            
            $count++;
        }
        
        $str .= " )";
        
        if ($this->getDescription()) {
            $str .= "\n\t".$this->getDescription();
        }
        
        if (count($this->getParams()) > 0) {
            $str .= "\n\tArguments:\n\t";
            $str .= implode("\n\t", $this->getParams());
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
     * Get method name.
     * 
     * @return string
     * @since  1.0
     */
    public function getName()
    {
        return $this->_array["name"];
    }
    
    /**
     * Get method access/visibility.
     * 
     * @return string Either 'public', 'protected' or 'private'.
     * @since  1.0
     */
    public function getAccess()
    {
        return $this->_array["access"];
    }
    
    /**
     * Get method's declaring class.
     * 
     * @return string
     * @since  1.0
     */
    public function getDeclaringClass()
    {
        return $this->_array["declaring_class"];
    }
    
    /**
     * Get params.
     * 
     * @return array
     * @since  1.0
     */
    public function getParams()
    {
        return $this->_array["params"];
    }
    
    /**
     * Get method return type.
     * 
     * @return string
     * @since  1.0
     */
    public function getReturnType()
    {
        return $this->_array["return"]["type"];
    }
    
    /**
     * Get method return description.
     * 
     * @return string
     * @since  1.0
     */
    public function getReturnDescription()
    {
        return $this->_array["return"]["description"];
    }
    
    /**
     * Get method since.
     * 
     * @return string
     * @since  1.0
     */
    public function getSince()
    {
        return $this->_array["since"];
    }
    
    /**
     * Get method description.
     * 
     * @return string
     * @since  1.0
     */
    public function getDescription()
    {
        return $this->_array["description"];
    }
    
    /**
     * Parse docblock text.
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
            
            if (preg_match('/^@(since|return)\s+(.+)/', $line, $matches)) {
                $this->_array[$matches[1]] = $matches[2];
            } elseif (!preg_match('/^@/', $line)) {
                $this->_array["description"] .= $line;
            }
        }
    }
}
