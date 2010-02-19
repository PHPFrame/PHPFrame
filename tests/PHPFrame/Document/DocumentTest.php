<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_DocumentTest extends PHPUnit_Framework_TestCase
{
    private $_reflector;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_reflector = new ReflectionClass("PHPFrame_Document");
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_interface()
    {
        $this->assertTrue($this->_reflector->implementsInterface("IteratorAggregate"));
        $this->assertTrue($this->_reflector->hasMethod("charset"));
        $this->assertTrue($this->_reflector->hasMethod("mime"));
        $this->assertTrue($this->_reflector->hasMethod("title"));
        $this->assertTrue($this->_reflector->hasMethod("appendTitle"));
        $this->assertTrue($this->_reflector->hasMethod("body"));
        $this->assertTrue($this->_reflector->hasMethod("appendBody"));
        $this->assertTrue($this->_reflector->hasMethod("prependBody"));
    }
    
    public function test_construct()
    {
        $contructor = $this->_reflector->getMethod("__construct");
        $this->assertTrue($contructor->isPublic());
        $this->assertEquals(1, $contructor->getNumberOfRequiredParameters());
        $this->assertEquals(2, $contructor->getNumberOfParameters());
        
        $params = $contructor->getParameters();
        $this->assertEquals("mime", $params[0]->getName());
        $this->assertEquals("charset", $params[1]->getName());
    }
}
