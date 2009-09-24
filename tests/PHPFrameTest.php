<?php
require "TestHelper.php";
TestHelper::initFramework();

class testPHPFrame extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        //...
    }
    
    function tearDown()
    {
        //...
    }
    
    function test_AppRegistry()
    {
        $app_registry = PHPFrame::AppRegistry();
        
        $this->assertType("PHPFrame_AppRegistry", $app_registry);
    }
    
    function test_Config()
    {
        $this->assertType("PHPFrame_Config", PHPFrame::Config());
    }
    
    function test_Mount()
    {
        PHPFrame::Mount();
        
        $this->assertEquals(3, PHPFrame::getRunLevel());
    }
    
    function test_DB()
    {
        $db = PHPFrame::DB();
        
        $this->assertType("PHPFrame_Database", $db);
    }
    
    function test_Session()
    {
        $session = PHPFrame::Session();
        
        $this->assertType("PHPFrame_SessionRegistry", $session);
    }
    
    function test_Request()
    {
        $request = PHPFrame::Request();
        
        $this->assertType("PHPFrame_RequestRegistry", $request);
    }
    
    function test_Response()
    {
        $response = PHPFrame::Response();
        
        $this->assertType("PHPFrame_Response", $response);
    }
}
