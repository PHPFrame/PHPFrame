<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_MailerTest extends PHPUnit_Framework_TestCase
{
    private $_mailer;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
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
}
