<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_ApplicationTest extends PHPUnit_Framework_TestCase
{
    private $_app;
    
    public function setUp()
    {
    	PHPFrame::setTestMode(true);
    	
    	// Get application install dir (we use CLI Tool for tests)
    	$pattern     = '/(.*)(\/|\\\)tests(\/|\\\)PHPFrame(\/|\\\)(.*)/';
        $replacement = '$1$2data$3CLI_Tool';
        $install_dir = preg_replace($pattern, $replacement, __FILE__);
        
        // Delete app registry if it exists
        if (is_file($install_dir.DS."tmp".DS."cache".DS."app.reg")) {
        	unlink($install_dir.DS."tmp".DS."cache".DS."app.reg");
        }
        
        // Instantiate application
        $options    = array("install_dir"=>$install_dir);
        $this->_app = new PHPFrame_Application($options);
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_getConfig()
    {
        $this->assertType("PHPFrame_Config", $this->_app->getConfig());
    }
    
    public function test_getRegistry()
    {
        $this->assertType("PHPFrame_FileRegistry", $this->_app->getRegistry());
    }
    
    public function test_getMailer()
    {
    	// Make sure mailer is enabled in config
    	$this->_app->getConfig()->set("smtp.enable", true);
    	
    	$mailer      = $this->_app->getMailer();
    	$smtp_config = $this->_app->getConfig()->getSection("smtp");
    	
        $this->assertType("PHPFrame_Mailer", $mailer);
        $this->assertEquals($smtp_config["mailer"], $mailer->Mailer);
        $this->assertEquals($smtp_config["host"], $mailer->Host);
        $this->assertEquals($smtp_config["user"], $mailer->Username);
        $this->assertEquals($smtp_config["pass"], $mailer->Password);
        $this->assertEquals($smtp_config["fromaddress"], $mailer->From);
        $this->assertEquals($smtp_config["fromname"], $mailer->FromName);
    }
    
    public function test_getMailerDisabled()
    {
        // Make sure mailer is disabled in config
        $this->_app->getConfig()->set("smtp.enable", false);
        
        $this->assertType("null", $this->_app->getMailer());
    }
    
    public function test_getLogger()
    {
        $this->assertType("PHPFrame_Logger", $this->_app->getLogger());
    }
    
    public function test_getInformer()
    {
    	// Make sure mailer is enabled in config
        $this->_app->getConfig()->set("smtp.enable", true);
        
        $this->_app->getConfig()->set("debug.informer_level", 1);
        $this->assertType("PHPFrame_Informer", $this->_app->getInformer());
    }
    
    public function test_getInformerMailerDisabled()
    {
    	$this->setExpectedException("LogicException");
    	
        $this->_app->getConfig()->set("debug.informer_level", 1);
        $this->assertType("PHPFrame_Informer", $this->_app->getInformer());
    }
    
    public function test_getProfiler()
    {
        $this->_app->getConfig()->set("debug.profiler_enable", 1);
        $this->assertType("PHPFrame_Profiler", $this->_app->getProfiler());
    }
    
    public function test_getDB()
    {
        $this->assertType("PHPFrame_Database", $this->_app->getDB());
    }
    
    public function test_getPermissions()
    {
        $this->assertType("PHPFrame_Permissions", $this->_app->getPermissions());
    }
    
    public function test_getLibraries()
    {
        $this->assertType("PHPFrame_Libraries", $this->_app->getLibraries());
    }
    
    public function test_getFeatures()
    {
        $this->assertType("PHPFrame_Features", $this->_app->getFeatures());
    }
    
    public function test_getPlugins()
    {
        $this->assertType("PHPFrame_Plugins", $this->_app->getPlugins());
    }
    
    public function test_getRequest()
    {
    	$this->assertType("PHPFrame_Request", $this->_app->getRequest());
    	$this->assertEquals("CLI", $this->_app->getRequest()->getMethod());
    }
    
    public function test_getResponse()
    {
    	$this->assertType("PHPFrame_Response", $this->_app->getResponse());
    	
    	// Check the response headers for the sake of testing
    	$headers = $this->_app->getResponse()->getHeaders();
    	
        $this->assertType("array", $headers);
        $this->assertArrayHasKey("X-Powered-By", $headers);
        $this->assertArrayHasKey("Expires", $headers);
        $this->assertArrayHasKey("Cache-Control", $headers);
        $this->assertArrayHasKey("Pragma", $headers);
        $this->assertArrayHasKey("Status", $headers);
        $this->assertArrayHasKey("Content-Language", $headers);
        $this->assertArrayHasKey("Content-Type", $headers);
        
        $this->assertEquals(1, preg_match('/^PHPFrame/', $headers["X-Powered-By"]));
        $this->assertEquals(200, $headers["Status"]);
        $this->assertEquals("en-GB", $headers["Content-Language"]);
    }
    
    public function test_dispatch()
    {
    	//$request = new PHPFrame_Request();
        //$this->_app->dispatch($request);
    }
}
