<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_MethodDocTest extends PHPUnit_Framework_TestCase
{
    private $_method_doc;
    
    public function setUp()
    {
        $this->_method_doc = new PHPFrame_MethodDoc(
            new ReflectionMethod("PHPFrame_ClassDoc", "__construct")
        );
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_getIterator()
    {
        $array = iterator_to_array($this->_method_doc);
        
        $this->assertType("array", $array);
        $this->assertArrayHasKey("name", $array);
        $this->assertArrayHasKey("access", $array);
        $this->assertArrayHasKey("declaring_class", $array);
        $this->assertArrayHasKey("params", $array);
        $this->assertArrayHasKey("return", $array);
        $this->assertArrayHasKey("description", $array);
    }
    
    public function test_getName()
    {
        $this->assertEquals("__construct", $this->_method_doc->getName());
    }
    
    public function test_getAccess()
    {
        $this->assertEquals("public", $this->_method_doc->getAccess());
    }
    
    public function test_getDeclaringClass()
    {
        $this->assertEquals("PHPFrame_ClassDoc", $this->_method_doc->getDeclaringClass());
    }
    
    public function test_getParams()
    {
        $params = $this->_method_doc->getParams();
        
        $this->assertType("array", $params);
        $this->assertArrayHasKey("reflection_obj", $params);
        $this->assertType("PHPFrame_ParamDoc", $params["reflection_obj"]);
        $this->assertArrayHasKey("visibility", $params);
        $this->assertType("PHPFrame_ParamDoc", $params["visibility"]);
        
    }
    
    public function test_getReturnType()
    {
        //var_dump($this->_method_doc->getReturnType()); exit;
    }
    
    public function test_getReturnDescription()
    {
        //print_r($this->_method_doc->getReturnDescription());
    }
    
    public function test_getSince()
    {
        //print_r($this->_method_doc->getSince());
    }
    
    public function test_getDescription()
    {
        //print_r($this->_method_doc->getDescription());
    }
}
