<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ViewHelperTest extends PHPUnit_Framework_TestCase
{
    private $_helper;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);
        
        $this->_app = new PHPFrame_Application(
            array("install_dir"=>$install_dir)
        );
        
        $this->_helper = $this->_app->factory()->getViewHelper("cli");
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
        if (is_dir($var_dir)) {
            rmdir($var_dir);
        }
    }
    
    public function test_()
    {
        $this->assertEquals(
            "Some heading\n------------", 
            $this->_helper->formatH2("Some heading")
        );
    }
}
