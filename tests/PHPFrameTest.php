<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrameTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        //...
    }
    
    function tearDown()
    {
        //...
    }
    
    function test_construct()
    {
        $refl_obj    = new ReflectionClass("PHPFrame");
        $constructor = $refl_obj->getMethod("__construct");
        
        // Ensure that constructor is private
        // This class shouldn't be instantiated
        $this->assertTrue($constructor->isPrivate());
    }
    
    function test_Version()
    {
        $this->assertType("string", PHPFrame::Version());
    }
    
    function test_setDataDirFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        PHPFrame::dataDir(1);
    }
}
