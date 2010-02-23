<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_SessionRegistryTest extends PHPUnit_Framework_TestCase
{
    private $_session;
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        
        $this->_session = PHPFrame::getSession();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_getClient()
    {
        $this->assertType("PHPFrame_Client", $this->_session->getClient());
    }
    
    public function test_getUser()
    {
        $this->assertType("PHPFrame_User", $this->_session->getUser());
    }
}
