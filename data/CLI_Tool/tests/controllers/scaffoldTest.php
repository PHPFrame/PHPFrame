<?php
// Include framework if not inculded yet
require_once preg_replace("/data\/.*/", "src/PHPFrame.php", __FILE__);

class ScaffoldControllerTest extends PHPUnit_Framework_TestCase
{
    private $_app;
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        
        $install_dir = preg_replace("/tests\/.*/", "", __FILE__);
        
        $this->_app = new PHPFrame_Application(array(
            "install_dir" => $install_dir
        ));
    }
    
    public function tearDown()
    {
        //...
        PHPFrame::getSession()->getSysevents()->clear();
    }
    
    public function test_usage()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("scaffold");
        $request->action("usage");
        
        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();
        
        $this->assertRegExp(
            "/Usage instructions/", 
            (string) $this->_app->response()
        );
    }
    
    public function test_create_table()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("scaffold");
        $request->action("create_table");
        $request->param("path", preg_replace("/data\/.*/", "src/PHPFrame/User/User.php", __FILE__));
        $request->param("drop", true);
        $request->param("install_dir", $this->_app->getInstallDir());
        
        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();
        
        $this->assertRegExp(
            "/SUCCESS: Database table successfully created/", 
            (string) $this->_app->response()
        );
    }
}
