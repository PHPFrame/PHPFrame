<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_RequestTest extends PHPUnit_Framework_TestCase
{
    private $_request;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_request = new PHPFrame_Request();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_a()
    {
        // Populate request using the session client
//        PHPFrame::getSession()->getClient()->populateRequest($this->_request);
//        
//        $this->assertEquals("CLI", $this->_request->getMethod());
    }
}
