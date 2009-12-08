<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_ViewTest extends PHPUnit_Framework_TestCase
{
    private $_view;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_view = new PHPFrame_View("test", array("somevar"=>"somevalue"));
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_()
    {
        $this->_view->addData("some_key","Some value");
        
        $array = iterator_to_array($this->_view);
        $this->assertType("array", $array);
        $this->assertEquals(2, count($array));
        $this->assertArrayHasKey("somevar", $array);
        $this->assertArrayHasKey("some_key", $array);
    }
}
