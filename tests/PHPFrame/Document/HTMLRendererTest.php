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
    
    public function test_render()
    {
        print_r($this->_renderer);
    }
    
    public function test_renderArray()
    {
        
    }
    
    public function test_renderAssoc()
    {
        
    }
    
    public function test_renderTraversable()
    {
        
    }
    
    public function test_renderObject()
    {
        
    }
    
    public function test_renderView()
    {
        
    }
}
