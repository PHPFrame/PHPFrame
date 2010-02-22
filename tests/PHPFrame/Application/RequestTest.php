<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_RequestTest extends PHPUnit_Framework_TestCase
{
    private $_request;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_request = new PHPFrame_Request();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_toString()
    {
    	$this->assertType("string", (string) $this->_request);
    }
    
    public function test_getIterator()
    {
    	$this->assertType("array", iterator_to_array($this->_request));
    }
    
    public function test_controllerName()
    {
    	$this->assertType("string", $this->_request->controllerName("index"));
    	$this->assertEquals("index", $this->_request->controllerName());
    }
    
    public function test_controllerNameException()
    {
    	$this->setExpectedException("InvalidArgumentException");
    	
        $this->assertType("string", $this->_request->controllerName("indexJJ"));
    }
    
    public function test_action()
    {
    	$this->assertType("string", $this->_request->action("index"));
        $this->assertEquals("index", $this->_request->action());
    }
    
    public function test_params()
    {
    	$this->assertType("array", $this->_request->params());
    }
    
    public function test_param()
    {
    	$this->assertType("string", $this->_request->param("myvar", "some value"));
        $this->assertEquals("some value", $this->_request->param("myvar"));
    }
    
    public function test_headers()
    {
    	$this->assertType("array", $this->_request->headers());
    }
    
    public function test_header()
    {
    	$this->assertType("string", $this->_request->header("Status", 200));
        $this->assertEquals("200", $this->_request->header("Status"));
        $this->assertArrayHasKey("Status", $this->_request->headers());
    }
    
    public function test_method()
    {
    	$this->assertType("string", $this->_request->method("CLI"));
        $this->assertEquals("CLI", $this->_request->method());
    }
    
    public function test_isPost()
    {
    	$this->assertFalse($this->_request->isPost());
    }
    
    public function test_isGet()
    {
    	$this->assertFalse($this->_request->isGet());
    }
    
    public function test_attachFile()
    {
    	
    }
    
    public function test_dettachFile()
    {
    	
    }
    
    public function test_files()
    {
    	
    }
    
    public function test_remoteAddr()
    {
        
    }
    public function test_requestURI()
    {
        
    }
    
    public function test_scriptName()
    {
        
    }
    
    public function test_queryString()
    {
        
    }
    
    public function test_requestTime()
    {
        
    }
    
    public function test_outfile()
    {
        
    }
    
    public function test_quiet()
    {
        
    }
    
    public function test_ajax()
    {
        //var_dump($this->_request->isPost());
    }
    
    public function test_dispatched()
    {
        
    }
}
