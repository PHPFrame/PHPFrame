<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

require_once "MockObject.php";

class PHPFrame_ObjectTest extends PHPUnit_Framework_TestCase
{
    private $_obj;
    
    public function setUp()
    {
        $this->_obj = new PHPFrame_MockObject();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_getException()
    {
        $this->setExpectedException('LogicException');
    	
        $a = $this->_obj->some_prop;
    }
    
    public function test_set()
    {
    	$this->setExpectedException('LogicException');
    	
        $this->_obj->some_prop = "some value";
    }
    
    public function test_getReflector()
    {
    	$this->assertType("ReflectionClass", $this->_obj->getReflector());
    }
    
    public function test_enforceArgumentTypes()
    {
    	$this->setExpectedException('LogicException');
    	
    	$this->_obj->foo("jhg", 1, true);
    }
    
    public function test_enforceReturnType()
    {
        $this->setExpectedException('RuntimeException');
        
        $this->_obj->bar("some string");
    }
}
