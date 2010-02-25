<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ApplicationTest extends PHPUnit_Framework_TestCase
{
    private $_app;
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        
        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::dataDir($data_dir);
        
        // Get application install dir (we use CLI Tool for tests)
        $pattern     = '/(.*)(\/|\\\)tests(\/|\\\)PHPFrame(\/|\\\)(.*)/';
        $replacement = '$1$2data$3CLI_Tool';
        $install_dir = preg_replace($pattern, $replacement, __FILE__);
        
        // Delete app registry if it exists
        if (is_file($install_dir.DS."tmp".DS."app.reg")) {
            unlink($install_dir.DS."tmp".DS."app.reg");
        }
        
        // Instantiate application
        $options    = array("install_dir"=>$install_dir);
        $this->_app = new PHPFrame_Application($options);
    }
    
    public function tearDown()
    {
        $tmp_dir = $this->_app->getInstallDir().DS."tmp";
        $app_reg = $tmp_dir.DS."app.reg";
        
        if (is_file($app_reg)) {
            unlink($app_reg);
        }
        if (is_dir($tmp_dir)) {
            PHPFrame_Filesystem::rm($tmp_dir, true);
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
    
    public function test_config()
    {
        $this->assertType("PHPFrame_Config", $this->_app->config());
    }
    
    public function test_registry()
    {
        $this->assertType("PHPFrame_FileRegistry", $this->_app->registry());
    }
    
    public function test_mailer()
    {
        // Make sure mailer is enabled in config
        $this->_app->config()->set("smtp.enable", true);
        
        $mailer      = $this->_app->mailer();
        $smtp_config = $this->_app->config()->getSection("smtp");
        
        $this->assertType("PHPFrame_Mailer", $mailer);
        $this->assertEquals($smtp_config["mailer"], $mailer->Mailer);
        $this->assertEquals($smtp_config["host"], $mailer->Host);
        $this->assertEquals($smtp_config["user"], $mailer->Username);
        $this->assertEquals($smtp_config["pass"], $mailer->Password);
        $this->assertEquals($smtp_config["fromaddress"], $mailer->From);
        $this->assertEquals($smtp_config["fromname"], $mailer->FromName);
    }
    
    public function test_mailerDisabled()
    {
        // Make sure mailer is disabled in config
        $this->_app->config()->set("smtp.enable", false);
        
        $this->assertType("null", $this->_app->mailer());
    }
    
    public function test_logger()
    {
        $this->assertType("PHPFrame_Logger", $this->_app->logger());
    }
    
    public function test_informer()
    {
        // Make sure mailer is enabled in config
        $this->_app->config()->set("smtp.enable", true);
        
        $this->_app->config()->set("debug.informer_level", 1);
        $this->assertType("PHPFrame_Informer", $this->_app->informer());
    }
    
    public function test_getInformerMailerDisabled()
    {
        $this->setExpectedException("LogicException");
        
        $this->_app->config()->set("debug.informer_level", 1);
        $this->assertType("PHPFrame_Informer", $this->_app->informer());
    }
    
    public function test_profiler()
    {
        $this->_app->config()->set("debug.profiler_enable", 1);
        $this->assertType("PHPFrame_Profiler", $this->_app->profiler());
    }
    
    public function test_db()
    {
        $this->assertType("PHPFrame_Database", $this->_app->db());
    }
    
    public function test_permissions()
    {
        $this->assertType("PHPFrame_Permissions", $this->_app->permissions());
    }
    
    public function test_libraries()
    {
        $this->assertType("PHPFrame_Libraries", $this->_app->libraries());
    }
    
    public function test_plugins()
    {
        $this->assertType("PHPFrame_Plugins", $this->_app->plugins());
    }
    
    public function test_request()
    {
        //$this->assertType("PHPFrame_Request", $this->_app->request());
        //$this->assertEquals("CLI", $this->_app->request()->getMethod());
    }
    
    public function test_response()
    {
        $this->assertType("PHPFrame_Response", $this->_app->response());
        
        // Check the response headers for the sake of testing
        $headers = $this->_app->response()->headers();
        
        $this->assertType("array", $headers);
        $this->assertArrayHasKey("X-Powered-By", $headers);
        $this->assertArrayHasKey("Expires", $headers);
        $this->assertArrayHasKey("Cache-Control", $headers);
        $this->assertArrayHasKey("Pragma", $headers);
        $this->assertArrayHasKey("Status", $headers);
        $this->assertArrayHasKey("Content-Language", $headers);
        $this->assertArrayHasKey("Content-Type", $headers);
        
        $this->assertEquals(1, preg_match('/PHPFrame/', $headers["X-Powered-By"]));
        $this->assertEquals(200, $headers["Status"]);
        $this->assertEquals("en-GB", $headers["Content-Language"]);
    }
    
    public function test_dispatch()
    {
        //$request = new PHPFrame_Request();
        //$this->_app->dispatch($request);
    }
}
