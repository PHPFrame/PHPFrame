<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ClassDocTest extends PHPUnit_Framework_TestCase
{
    private $_class_doc;
    
    public function setUp()
    {
        $this->_class_doc = new PHPFrame_ClassDoc(
            new ReflectionClass("PHPFrame_ClassDoc")
        );
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_getIterator()
    {
        $array = iterator_to_array($this->_class_doc);
        
        $this->assertType("array", $array);
        $this->assertArrayHasKey("class_name", $array);
        $this->assertArrayHasKey("props", $array);
        $this->assertArrayHasKey("methods", $array);
    }
    
    public function test_getClassName()
    {
        $this->assertEquals("PHPFrame_ClassDoc", $this->_class_doc->getClassName());
    }
    
    public function test_getMethods()
    {
        $methods = $this->_class_doc->getMethods();
        
        $this->assertType("array", $methods);
        $this->assertEquals(2, count($methods));
        $this->assertArrayHasKey("own", $methods);
        $this->assertType("array", $methods["own"]);
        $this->assertType("PHPFrame_MethodDoc", $methods["own"][0]);
        $this->assertArrayHasKey("inherited", $methods);
        $this->assertType("array", $methods["inherited"]);
    }
    
    public function test_getOwnMethods()
    {
        $methods = $this->_class_doc->getOwnMethods();
        
        $this->assertType("array", $methods);
        $this->assertType("PHPFrame_MethodDoc", $methods[0]);
    }
    
    public function test_getInheritedMethods()
    {
        $methods = $this->_class_doc->getInheritedMethods();
        
        $this->assertType("array", $methods);
    }
    
    public function test_getProps()
    {
        $props = $this->_class_doc->getProps();
        
        $this->assertType("array", $props);
    }
}
