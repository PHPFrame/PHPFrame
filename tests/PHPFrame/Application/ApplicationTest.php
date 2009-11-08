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
    	$pattern     = '/(.*)(\/|\\\)tests(\/|\\\)PHPFrame(\/|\\\)(.*)/';
    	$replacement = '$1$2data$3CLI_Tool';
    	$install_dir = preg_replace($pattern, $replacement, __FILE__);
    	$options     = array("install_dir"=>$install_dir);
        $this->_app  = new PHPFrame_Application($options);
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
        $this->assertType("PHPFrame_Mailer", $this->_app->getMailer());
    }
    
    public function test_getLog()
    {
        $this->assertType("PHPFrame_Logger", $this->_app->getLog());
    }
    
    public function test_getInformer()
    {
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
    
    public function test_Fire()
    {
    	
    }
}
