<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ActionControllerTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_factory, $_controller;
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        
        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);
        
        $this->_app = new PHPFrame_Application(
            array("install_dir"=>$install_dir)
        );
        
        $this->_controller = $this->_app->factory()->getActionController("man");
    }
    
    public function tearDown()
    {
        //...
        $tmp_dir = $this->_app->getInstallDir().DS."tmp";
        $app_reg = $tmp_dir.DS."app.reg";
        
        if (is_file($app_reg)) {
            unlink($app_reg);
        }
        if (is_dir($tmp_dir)) {
            rmdir($tmp_dir);
        }
        
        $var_dir = $this->_app->getInstallDir().DS."var";
        $app_log = $var_dir.DS."app.log";
        $data_db = $var_dir.DS."data.db";
        
        if (is_file($app_log)) {
            unlink($app_log);
        }
        if (is_file($data_db)) {
            unlink($data_db);
        }
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
