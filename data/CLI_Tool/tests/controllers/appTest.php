<?php
// Include framework if not inculded yet
require_once preg_replace("/data\/.*/", "src/PHPFrame.php", __FILE__);

class AppControllerTest extends PHPUnit_Framework_TestCase
{
    private $_app;
    private $_newapp_dir;
    
    public function __construct()
    {
        $this->_newapp_dir = PHPFrame_Filesystem::getSystemTempDir().DS."newapp";
    }
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        PHPFrame::dataDir(preg_replace("/CLI_Tool\/.*/", "", __FILE__));
        PHPFrame::getSession()->getSysevents()->clear();
        
        $install_dir = preg_replace("/tests\/.*/", "", __FILE__);
        
        $this->_app = new PHPFrame_Application(array(
            "install_dir" => $install_dir
        ));
        
        if (is_dir($this->_newapp_dir)) {
            PHPFrame_Filesystem::rm($this->_newapp_dir, true);
        }
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_create()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("app");
        $request->action("create");
        $request->param("app_name", "MyApp");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/SUCCESS: App created successfully/", $output);
        
        PHPFrame_Filesystem::rm($this->_newapp_dir, true);
    }
    
    public function test_createDirNotEmpty()
    {
        if (!is_dir($this->_newapp_dir)) {
            mkdir($this->_newapp_dir);
        }
        
        touch($this->_newapp_dir.DS."file1.txt");
        touch($this->_newapp_dir.DS."file2.txt");
        
        $request = new PHPFrame_Request();
        $request->controllerName("app");
        $request->action("create");
        $request->param("app_name", "MyApp");
        $request->param("install_dir", $this->_newapp_dir);
        $request->param("allow_non_empty_dir", true);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/SUCCESS: App created successfully/", $output);
        
        PHPFrame_Filesystem::rm($this->_newapp_dir, true);
    }
    
    public function test_createFailureDirNotEmpty()
    {
        if (!is_dir($this->_newapp_dir)) {
            mkdir($this->_newapp_dir);
        }
        
        touch($this->_newapp_dir.DS."file1.txt");
        touch($this->_newapp_dir.DS."file2.txt");
        
        $request = new PHPFrame_Request();
        $request->controllerName("app");
        $request->action("create");
        $request->param("app_name", "MyApp");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/ERROR: Target directory is not empty/", $output);
    }
    
    public function test_createFailureUnknownTemplate()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("app");
        $request->action("create");
        $request->param("app_name", "MyApp");
        $request->param("install_dir", $this->_newapp_dir);
        $request->param("template", "blah");
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/ERROR: Unknown app template 'blah'/", $output);
    }
    
    public function test_remove()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("app");
        $request->action("create");
        $request->param("app_name", "MyApp");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/SUCCESS: App created successfully/", $output);
        
        // Now that we have installed we can test the 'remove' action
        $request = new PHPFrame_Request();
        $request->controllerName("app");
        $request->action("remove");
        $request->param("install_dir", $this->_newapp_dir);
        
        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_clean();
        
        $this->assertRegExp("/SUCCESS: App removed successfully/", $output);
    }
}
