<?php
// Include framework if not inculded yet
require_once preg_replace("/data\/.*/", "src/PHPFrame.php", __FILE__);

class ScaffoldControllerTest extends PHPUnit_Framework_TestCase
{
    private $_app;
    private $_newapp_dir;
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        PHPFrame::dataDir(preg_replace("/CLI_Tool\/.*/", "", __FILE__));
        PHPFrame::getSession()->getSysevents()->clear();
        
        $install_dir = preg_replace("/tests\/.*/", "", __FILE__);
        
        $this->_app = new PHPFrame_Application(array(
            "install_dir" => $install_dir
        ));
        
        // Create an new app to run the tests on
        $this->_newapp_dir = PHPFrame_Filesystem::getSystemTempDir().DS."newapp";
        if (is_dir($this->_newapp_dir)) {
            PHPFrame_Filesystem::rm($this->_newapp_dir, true);
        } 
        
        $request = new PHPFrame_Request();
        $request->controllerName("app");
        $request->action("create");
        $request->param("app_name", "TestApp");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_table()
    {
        // Enable db in config file of test app
        $request = new PHPFrame_Request();
        $request->controllerName("config");
        $request->action("set");
        $request->param("key", "db.enable");
        $request->param("value", "1");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/db.enable: 1/", $output);
        
        // Create a table for the base User class
        $request = new PHPFrame_Request();
        $request->controllerName("scaffold");
        $request->action("table");
        $request->param("path", preg_replace("/data\/.*/", "src/PHPFrame/User/User.php", __FILE__));
        $request->param("drop", true);
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/SUCCESS: Database table successfully created/", $output);
    }
    
    public function test_persistent()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("scaffold");
        $request->action("persistent");
        $request->param("name", "Post");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/SUCCESS: Class file created/", $output);
    }
    
//    public function test_mapper()
//    {
//        $request = new PHPFrame_Request();
//        $request->controllerName("scaffold");
//        $request->action("mapper");
//        $request->param("class", "Post");
//        $request->param("install_dir", $this->_newapp_dir);
//        
//        ob_start();
//        $this->_app->dispatch($request);
//        $output = ob_get_clean();
//        
//        $this->assertRegExp("/SUCCESS: Class file created/", $output);
//    }
    
    public function test_controller()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("scaffold");
        $request->action("controller");
        $request->param("name", "Blog");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/SUCCESS: Class file created/", $output);
    }
    
    public function test_helper()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("scaffold");
        $request->action("helper");
        $request->param("name", "Blog");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/SUCCESS: Class file created/", $output);
    }
    
    public function test_plugin()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("scaffold");
        $request->action("plugin");
        $request->param("name", "BlogRouter");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/SUCCESS: Class file created/", $output);
    }
}
