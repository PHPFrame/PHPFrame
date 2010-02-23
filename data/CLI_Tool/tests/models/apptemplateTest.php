<?php
// Include framework if not inculded yet
require_once preg_replace("/data\/.*/", "src/PHPFrame.php", __FILE__);
// Include file containing class to test
require_once preg_replace("/tests\/.*/", "src/models/apptemplate.php", __FILE__);

class AppTemplateTest extends PHPUnit_Framework_TestCase
{
    private $_install_dir;
    private $_app_template;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_install_dir  = PHPFrame_Filesystem::getSystemTempDir();
        $this->_install_dir .= DS."apptemplate-test";
        
        if (is_dir($this->_install_dir)) {
            PHPFrame_Filesystem::rm($this->_install_dir, true);
        }
        
        $this->_app_template = new AppTemplate($this->_install_dir);
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_install()
    {
        $this->_app_template->install("myapp");
        
        $this->assertTrue(is_dir($this->_install_dir.DS."etc"));
        $this->assertTrue(is_dir($this->_install_dir.DS."public"));
        $this->assertTrue(is_dir($this->_install_dir.DS."src"));
        $this->assertTrue(is_dir($this->_install_dir.DS."tmp"));
        $this->assertTrue(is_dir($this->_install_dir.DS."var"));
        $this->assertTrue(is_file($this->_install_dir.DS."etc".DS."phpframe.ini"));
    }
    
    public function test_remove()
    {
    	$this->_app_template->install("myapp");
    	
    	$this->assertTrue(is_dir($this->_install_dir.DS."etc"));
        $this->assertTrue(is_dir($this->_install_dir.DS."public"));
        $this->assertTrue(is_dir($this->_install_dir.DS."src"));
        $this->assertTrue(is_dir($this->_install_dir.DS."tmp"));
        $this->assertTrue(is_dir($this->_install_dir.DS."var"));
        $this->assertTrue(is_file($this->_install_dir.DS."etc".DS."phpframe.ini"));
        
        $this->_app_template->remove();
        
        $this->assertFalse(is_dir($this->_install_dir.DS."etc"));
        $this->assertFalse(is_dir($this->_install_dir.DS."public"));
        $this->assertFalse(is_dir($this->_install_dir.DS."src"));
        $this->assertFalse(is_dir($this->_install_dir.DS."tmp"));
        $this->assertFalse(is_dir($this->_install_dir.DS."var"));
    }
}
