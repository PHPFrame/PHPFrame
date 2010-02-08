<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_MailerTest extends PHPUnit_Framework_TestCase
{
    private $_mailer;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::setDataDir($data_dir);
        
        $this->_mailer = new PHPFrame_Mailer();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_messageIdSuffix()
    {
        $this->_mailer->setMessageIdSuffix("someid");
        
        $this->assertEquals("someid", $this->_mailer->getMessageIdSuffix());
        
        // Get header to check that suffix is added appended to the message id
        // and encoded to base64
        $header = $this->_mailer->CreateHeader();
        preg_match('/Message\-Id\: <.*\-(.*)@.*>/', $header, $matches);
        $this->assertEquals("someid", base64_decode($matches[1]));
    }
    
    public function test_serialise()
    {
        $serialised   = serialize($this->_mailer);
        $unserialised = unserialize($serialised);
        
        $this->assertEquals($this->_mailer, $unserialised);
    }
}
