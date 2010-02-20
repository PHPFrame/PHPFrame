<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_HTMLRendererTest extends PHPUnit_Framework_TestCase
{
    private $_renderer;
    
    public function setUp()
    {
        $this->_renderer = new PHPFrame_HTMLRenderer(dirname(__FILE__));
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_renderBool()
    {
    	$this->assertEquals("1", $this->_renderer->render(true));
        $this->assertEquals("", $this->_renderer->render(false));
    }
    
    public function test_renderNumber()
    {
        $array = array(1, 345, -345, 3.14, -3.14);
        
        foreach ($array as $value) {
            $this->assertEquals($value, $this->_renderer->render($value));
        }
    }
    
    public function test_renderString()
    {
        $array = array("some string");
        
        foreach ($array as $value) {
            $this->assertEquals($value, $this->_renderer->render($value));
        }
    }
    
    public function test_renderArray()
    {
        $values = array(array(1,2,3), array(1,2,3, array(1,2)));
        $expected = "Array";
        
        foreach ($values as $value) {
            $this->assertEquals($expected, $this->_renderer->render($value));
        }
    }
    
    public function test_renderView()
    {
        $view = new PHPFrame_View("view", array(
            "title" => "My home page",
            "body"  => "Blah blah blah..."
        ));
        
        $this->assertEquals(
            "<h2>My home page</h2>

<div>
Blah blah blah...</div>
", 
            $this->_renderer->render($view)
        );
    }
}
