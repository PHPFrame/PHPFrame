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
        $value = array(1,2,3);
        $expected = "<root>
    <array>1</array>
    <array>2</array>
    <array>3</array>
</root>
";
        
        $this->assertEquals($expected, $this->_renderer->render($value));
        
        $value = array(1,2,3, array(1,2));
        $expected = "<root>
    <array>1</array>
    <array>2</array>
    <array>3</array>
    <array>
        <array>1</array>
        <array>2</array>
    </array>
</root>
";
        
        $this->assertEquals($expected, $this->_renderer->render($value));
        //var_dump($this->_renderer->render($value));
    }
    
    public function test_renderAssoc()
    {
        $value = array("key"=>"value", "key2"=>"value2");
        $expected = "<root>
    <key>value</key>
    <key2>value2</key2>
</root>
";
        
        $this->assertEquals($expected, $this->_renderer->render($value));
        //var_dump($this->_renderer->render($value));
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
