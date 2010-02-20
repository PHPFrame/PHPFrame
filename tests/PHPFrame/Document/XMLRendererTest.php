<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_XMLRendererTest extends PHPUnit_Framework_TestCase
{
    private $_renderer;
    
    public function setUp()
    {
        $this->_renderer = new PHPFrame_XMLRenderer();
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
