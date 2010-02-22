<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ActionControllerTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_factory, $_controller;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);
        
        $this->_app = new PHPFrame_Application(
            array("install_dir"=>$install_dir)
        );
        
        $this->_controller = $this->_app->factory()->getActionController("man");
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_execute()
    {
        // We HAVE TO pass a new request, otherwise the app will try to 
        // populate a new one using the CLIClient and this creates a conflict
        // with PHPUnit. 
        $request = new PHPFrame_Request();
        $this->_app->request($request)->controllerName("man");
        
        $this->assertEquals("", (string) $this->_app->response()->document());
        
        $this->_controller->execute($this->_app);
        
        $pattern = "/PHPFrame Command Line Tool/";
        $this->assertRegExp($pattern, (string) $this->_app->response()->document());
        $this->assertTrue($this->_controller->getSuccess());
    }
}
