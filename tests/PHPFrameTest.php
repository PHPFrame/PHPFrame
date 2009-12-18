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
    
    function test_Version()
    {
        $this->assertType("string", PHPFrame::Version());
    }
}
