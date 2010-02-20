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
    }
    
    public function test_()
    {
        $this->assertEquals(
            "Some heading\n------------", 
            $this->_helper->formatH2("Some heading")
        );
    }
}
