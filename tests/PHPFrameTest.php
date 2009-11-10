<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-1));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

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
