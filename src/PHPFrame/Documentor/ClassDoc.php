<?php
/**
 * PHPFrame/Documentor/ClassDoc.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Documentor
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://github.com/PHPFrame/PHPFrame
 * @ignore
 */

/**
 * Class Documentor Class
 * 
 * @category PHPFrame
 * @package  Documentor
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_ClassDoc implements IteratorAggregate
{
    const VISIBILITY_PUBLIC    = 1;
    const VISIBILITY_PROTECTED = 2;
    const VISIBILITY_PRIVATE   = 4;
    
    private $_visibility = self::VISIBILITY_PUBLIC;
    private $_array = array(
        "class_name" => null,
        "props" => array(),
        "methods" => array("own"=>array(), "inherited"=>array())
    );
    
    public function __construct(
        ReflectionClass $reflection_obj, 
        $visibility=self::VISIBILITY_PUBLIC
    )
    {
        $this->_visibility = $visibility;
        
        $this->_array["class_name"] = $reflection_obj->getName();
        
        foreach ($reflection_obj->getProperties() as $reflection_prop) {
            if (
                ($reflection_prop->isProtected() 
                && $this->_visibility < PHPFrame_ClassDoc::VISIBILITY_PROTECTED)
                ||
                ($reflection_prop->isPrivate()
                && $this->_visibility < PHPFrame_ClassDoc::VISIBILITY_PRIVATE)
            ) {
                continue;
            }
            
            $this->_array["props"][] = new PHPFrame_PropertyDoc($reflection_prop);
        }
        
        foreach ($reflection_obj->getMethods() as $reflection_method) {
            if (
                ($reflection_method->isProtected() 
                && $this->_visibility < PHPFrame_ClassDoc::VISIBILITY_PROTECTED)
                ||
                ($reflection_method->isPrivate()
                && $this->_visibility < PHPFrame_ClassDoc::VISIBILITY_PRIVATE)
            ) {
                continue;
            }
            
            $method_doc = new PHPFrame_MethodDoc($reflection_method);
            
            if ($method_doc->getDeclaringClass() == $this->getClassName()) {
                $this->_array["methods"]["own"][] = $method_doc;
            } else {
                $this->_array["methods"]["inherited"][] = $method_doc;
            }
        }
    }
    
    public function __toString()
    {
        $str  = "Class: ".$this->getClassName()."\n";
        for ($i=0; $i<(strlen($this->getClassName())+7); $i++) {
            $str .= "-";
        }
        
        if (count($this->getProps()) > 0) {
            $str .= "\n\nProperties:\n";
            $str .= "-----------\n\n";
            $str .= implode("\n\n", $this->getProps());
        }
        
        if (count($this->getOwnMethods()) > 0) {
            $str .= "\nMethods:\n";
            $str .= "--------\n\n";
            $str .= implode("\n\n", $this->getOwnMethods());
        }
        
        if (count($this->getInheritedMethods()) > 0) {
            $str .= "\n\nInherited Methods:\n";
            $str .= "------------------\n\n";
            $str .= implode("\n\n", $this->getInheritedMethods());
        }
        
        return $str;
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->_array);
    }
    
    public function getClassName()
    {
        return $this->_array["class_name"];
    }
    
    public function getMethods()
    {
        return $this->_array["methods"];
    }
    
    public function getOwnMethods()
    {
        return $this->_array["methods"]["own"];
    }
    
    public function getInheritedMethods()
    {
        return $this->_array["methods"]["inherited"];
    }
    
    public function getProps()
    {
        return $this->_array["props"];
    }
}
