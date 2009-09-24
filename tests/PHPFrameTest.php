<?php
$src_dir = str_replace(DIRECTORY_SEPARATOR."tests", "", dirname(__FILE__));
$src_dir .= DIRECTORY_SEPARATOR."src";
require_once $src_dir.DIRECTORY_SEPARATOR."PHPFrame.php";

// Set constant with app specific path to tmp directory
define("PHPFRAME_TMP_DIR", dirname(__FILE__).DIRECTORY_SEPARATOR."tmp");
// Initialise PHPFrame environment
PHPFrame::Env();
PHPFrame::Response();

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
    
    function test_MountSQLiteFallbackNoVarDir()
    {
        try {
            PHPFrame::Mount();
        } 
        catch (LogicException $expected) {
            return;
        }
 
        $this->fail('An expected exception has not been raised.');
    }
    
    function test_Mount()
    {
        define("PHPFRAME_VAR_DIR", dirname(__FILE__).DIRECTORY_SEPARATOR."var");
        
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
