<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_XMLDocumentTest extends PHPUnit_Framework_TestCase
{
    private $_document;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_document = new PHPFrame_XMLDocument();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_()
    {
        //print_r($this->_document);
    }
    
    public function test_getCharset()
    {
        $this->assertEquals("UTF-8", $this->_document->getCharset());
    }
    
    public function test_getMimeType()
    {
        $this->assertEquals("text/xml", $this->_document->getMimeType());
    }
    
    public function test_setTitle()
    {
        $title = "Lorem ipsum";
        $this->_document->setTitle($title);
        
        $this->assertEquals($title, $this->_document->getTitle());
    }
    
    public function test_appendTitle()
    {
        $title = "Lorem ipsum";
        $this->_document->setTitle($title);
        $this->_document->appendTitle($title);
        
        $this->assertEquals($title.$title, $this->_document->getTitle());
    }
    
//    public function test_setBody()
//    {
//        $body = "Lorem ipsum...";
//        $this->_document->setBody($body);
//        
//        $this->assertEquals($body, $this->_document->getBody());
//    }
//    
//    public function test_appendBody()
//    {
//        $body = "Lorem ipsum";
//        $this->_document->setBody($body);
//        $this->_document->appendBody($body);
//        
//        $this->assertEquals($body.$body, $this->_document->getBody());
//    }
//    
//    public function prependBody()
//    {
//        $body = "Lorem ipsum";
//        $this->_document->setBody($body);
//        $this->_document->prependBody("Blah");
//        
//        $this->assertEquals("Blah".$body, $this->_document->getBody());
//    }
    
    public function test_toString()
    {
        $this->_document->setTitle("The title");
        $container = $this->_document->addNode("container", null);
        $this->_document->addNode("item", $container, array("name"=>"item1"));
        $this->_document->addNode("item", $container, null, "Some content");
        $this->_document->addNode("item", $container);
        
        $str = (string) $this->_document;
        $xml = "<container>
    <item name=\"item1\" />
    <item>Some content</item>
    <item />
</container>";
        
        $this->assertXmlStringEqualsXmlString($xml, $str);
    }
}
