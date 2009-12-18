<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

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
