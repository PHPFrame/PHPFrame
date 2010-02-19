<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_HTMLDocumentTest extends PHPUnit_Framework_TestCase
{
    private $_document;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_document = new PHPFrame_HTMLDocument();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_()
    {
        //print_r($this->_document);
    }
    
    public function test_getDocType()
    {
        $doc_type = $this->_document->getDocType();
        $this->assertType("DOMDocumentType", $doc_type);
        $this->assertEquals("html", $doc_type->name);
    }
}
