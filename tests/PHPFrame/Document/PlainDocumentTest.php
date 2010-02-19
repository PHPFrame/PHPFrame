<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_PlainDocumentTest extends PHPUnit_Framework_TestCase
{
    private $_document;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_document = new PHPFrame_PlainDocument();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_construct()
    {
    	$doc = new PHPFrame_PlainDocument("text/plain", "ISO-8859-1");
    	
    	$this->assertEquals("ISO-8859-1", $doc->charset());
    }
    
    public function test_charset()
    {
        $this->assertEquals("UTF-8", $this->_document->charset());
    }
    
    public function test_mime()
    {
        $this->assertEquals("text/plain", $this->_document->mime());
    }
    
    public function test_title()
    {
        $title = "Lorem ipsum";
        $this->_document->title($title);
        
        $this->assertEquals($title, $this->_document->title());
    }
    
    public function test_appendTitle()
    {
        $title = "Lorem ipsum";
        $this->_document->title($title);
        $this->_document->appendTitle($title);
        
        $this->assertEquals($title.$title, $this->_document->title());
    }
    
    public function test_body()
    {
        $body = "Lorem ipsum...";
        $this->_document->body($body);
        
        $this->assertEquals($body, $this->_document->body());
    }
    
    public function test_appendBody()
    {
        $body = "Lorem ipsum";
        $this->_document->body($body);
        $this->_document->appendBody($body);
        
        $this->assertEquals($body.$body, $this->_document->body());
    }
    
    public function prependBody()
    {
        $body = "Lorem ipsum";
        $this->_document->body($body);
        $this->_document->prependBody("Blah");
        
        $this->assertEquals("Blah".$body, $this->_document->body());
    }
    
    public function test_toString()
    {
        $this->_document->title("The title");
        $this->_document->body("Blah blah blah blah");
        
        $str = (string) $this->_document;
        
        $this->assertEquals(1, preg_match("/The title/", $str));
    }
    
    public function test_toStringWithSysevents()
    {
        $this->_document->title("The title");
        $this->_document->body("Blah blah blah blah");
        
        PHPFrame::getSession()->getSysevents()->append("Some message");
        
        $str = (string) $this->_document;
        
        $this->assertEquals(1, preg_match("/INFO: Some message/", $str));
    }
    
    public function test_getIterator()
    {
    	$title = "A nice title";
    	$body  = "Some really nice content...";
    	
    	$this->_document->title($title);
    	$this->_document->body($body);
    	
    	$array = iterator_to_array($this->_document);
    	
    	$this->assertEquals(4, count($array));
    	$this->assertArrayHasKey("mime_type", $array);
    	$this->assertArrayHasKey("charset", $array);
    	$this->assertArrayHasKey("title", $array);
    	$this->assertArrayHasKey("body", $array);
    	$this->assertEquals("text/plain", $array["mime_type"]);
    	$this->assertEquals("UTF-8", $array["charset"]);
    	$this->assertEquals($title, $array["title"]);
    	$this->assertEquals($body, $array["body"]);
    }
}
