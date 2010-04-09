<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

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

    public function test_enforceArguments()
    {
        $this->setExpectedException('LogicException');

        $this->_obj->foo("jhg", 1, true);
    }

    public function test_enforceArgumentsNoDocBlock()
    {
        $this->setExpectedException('LogicException');

        $this->_obj->bar("jhg", true, false);
    }

    public function test_enforceReturnType()
    {
        $this->setExpectedException('RuntimeException');

        $this->_obj->bar("some string");
    }

    public function test_enforceReturnTypeNoDocBlock()
    {
        $this->setExpectedException('LogicException');

        $this->_obj->someMethod();
    }
}
