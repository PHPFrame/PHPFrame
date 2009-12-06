<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_ResponseTest extends PHPUnit_Framework_TestCase
{
    private $_response;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_response = new PHPFrame_Response();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_toString()
    {
        $this->assertType("string", $this->_response->__toString());
    }
    
    public function test_setStatusCode()
    {
        $array = array(200, 301, 302, 303, 400, 401, 403, 404, 500, 501);
        
        foreach ($array as $code) {
            $this->_response->setStatusCode($code);
            $this->assertEquals($code, $this->_response->getHeader("Status"));
        }
        
        // Revert code to its original state (200)
        $this->_response->setStatusCode(200);
        $this->assertEquals(200, $this->_response->getHeader("Status"));
    }
    
    public function test_setStatusCodeFailure()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_response->setStatusCode(1);
    }
    
    public function test_getHeaders()
    {
        // Check the response headers
        $headers = $this->_response->getHeaders();
        
        $this->assertType("array", $headers);
        $this->assertArrayHasKey("X-Powered-By", $headers);
        $this->assertArrayHasKey("Expires", $headers);
        $this->assertArrayHasKey("Cache-Control", $headers);
        $this->assertArrayHasKey("Pragma", $headers);
        $this->assertArrayHasKey("Status", $headers);
        $this->assertArrayHasKey("Content-Language", $headers);
        $this->assertArrayHasKey("Content-Type", $headers);
        
        $this->assertEquals(1, preg_match('/^PHPFrame/', $headers["X-Powered-By"]));
        $this->assertEquals(200, $headers["Status"]);
    }
    
    public function test_getHeader()
    {
        $this->assertEquals(1, preg_match('/^PHPFrame/', $this->_response->getHeader("X-Powered-By")));
        $this->assertEquals(200, $this->_response->getHeader("Status"));
    }
    
    public function test_setHeader()
    {
        $this->_response->setHeader("Status", 501);
        $this->assertEquals(501, $this->_response->getHeader("Status"));
        
        $this->_response->setHeader("Status", 200);
        $this->assertEquals(200, $this->_response->getHeader("Status"));
    }
    
    public function test_getDocument()
    {
        $this->assertType("PHPFrame_Document", $this->_response->getDocument());
    }
    
    public function test_setDocument()
    {
        $this->_response->setDocument(new PHPFrame_PlainDocument());
        $this->assertType("PHPFrame_PlainDocument", $this->_response->getDocument());
        
        $this->_response->setDocument(new PHPFrame_HTMLDocument());
        $this->assertType("PHPFrame_HTMLDocument", $this->_response->getDocument());
        
        $this->_response->setDocument(new PHPFrame_PlainDocument());
        $this->assertType("PHPFrame_PlainDocument", $this->_response->getDocument());
    }
    
    public function test_getRenderer()
    {
        $this->assertType("PHPFrame_IRenderer", $this->_response->getRenderer());
    }
    
    public function test_setRenderer()
    {
        $this->_response->setRenderer(new PHPFrame_PlainRenderer());
        $this->assertType("PHPFrame_PlainRenderer", $this->_response->getRenderer());
        
        $this->_response->setRenderer(new PHPFrame_HTMLRenderer("somepath"));
        $this->assertType("PHPFrame_HTMLRenderer", $this->_response->getRenderer());
        
        $this->_response->setRenderer(new PHPFrame_PlainRenderer());
        $this->assertType("PHPFrame_PlainRenderer", $this->_response->getRenderer());
    }
    
    public function test_getPathway()
    {
        $this->assertType("PHPFrame_Pathway", $this->_response->getPathway());
    }
    
    public function test_send()
    {
        
    }
}
