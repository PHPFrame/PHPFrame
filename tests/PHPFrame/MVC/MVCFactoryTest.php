<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_MVCFactoryTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_factory;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);
        
        $this->_app = new PHPFrame_Application(
            array("install_dir"=>$install_dir)
        );
        
        $this->_factory = new PHPFrame_MVCFactory($this->_app);
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_getActionController()
    {
        $controller = $this->_factory->getActionController("app");
        
        $this->assertType("AppController", $controller);
    }
    
    public function test_getActionControllerReflectionException()
    {
        $this->setExpectedException("ReflectionException");
        
        $controller = $this->_factory->getActionController("apppp");
    }
    
    public function test_view()
    {
        $view = $this->_factory->view("index");
        
        $this->assertType("PHPFrame_View", $view);
    }
    
    public function test_getViewPassData()
    {
        $view = $this->_factory->view("index", array("key"=>"value"));
        $data = $view->getData();
        
        $this->assertType("PHPFrame_View", $view);
        $this->assertType("array", $data);
        $this->assertEquals(1, count($data));
        $this->assertArrayHasKey("key", $data);
        $this->assertEquals("value", $data["key"]);
    }
    
    public function test_getViewPassDataFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $view = $this->_factory->view("index", array("value"));
    }
}
