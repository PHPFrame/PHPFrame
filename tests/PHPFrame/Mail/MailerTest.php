<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_MailerTest extends PHPUnit_Framework_TestCase
{
    private $_mailer;
    private $_config;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $basedir = preg_replace("/tests\/.*/", "", __FILE__);
        PHPFrame::dataDir($basedir."data");

        $this->_mailer = new PHPFrame_Mailer();
        $this->_config = new PHPFrame_Config($basedir."tests".DS."tests.ini");
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

    public function test_serialiseAfterSend()
    {
        $this->_mailer = new PHPFrame_Mailer($this->_config->getSection("smtp"));

        $this->_mailer->Subject = "Test email";
        $this->_mailer->Body    = "This email was sent by a unit test.";
        $this->_mailer->AddAddress("lupo@e-noise.com");
        $this->_mailer->send();

        $serialised   = serialize($this->_mailer);
        $unserialised = unserialize($serialised);

        $this->assertEquals($this->_mailer, $unserialised);
    }
}
