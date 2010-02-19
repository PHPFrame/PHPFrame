<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_RPCDocumentTest extends PHPUnit_Framework_TestCase
{
    private $_document;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_document = new PHPFrame_RPCDocument();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_()
    {
        //print_r($this->_document);
    }
}
