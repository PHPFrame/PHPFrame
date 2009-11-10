<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_ResponseTest extends PHPUnit_Framework_TestCase
{
    private $_response;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_response = new PHPFrame_Response();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_()
    {
        // Check the response headers for the sake of testing
        $headers = $this->_response->getHeaders();
        
        $this->assertType("array", $headers);
        $this->assertArrayHasKey("X-Powered-By", $headers);
        $this->assertArrayHasKey("Expires", $headers);
        $this->assertArrayHasKey("Cache-Control", $headers);
        $this->assertArrayHasKey("Pragma", $headers);
        $this->assertArrayHasKey("Status", $headers);
        $this->assertArrayHasKey("Content-Language", $headers);
        $this->assertArrayHasKey("Content-Type", $headers);
        
        $this->assertEquals(1, preg_match('/^PHPFrame/', $headers["X-Powered-By"]));
        $this->assertEquals(200, $headers["Status"]);
    }
}
