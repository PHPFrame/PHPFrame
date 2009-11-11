<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_SessionRegistryTest extends PHPUnit_Framework_TestCase
{
    private $_session;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_session = PHPFrame::Session();
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
