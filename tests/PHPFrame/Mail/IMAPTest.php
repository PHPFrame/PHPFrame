<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_IMAPTest extends PHPUnit_Framework_TestCase
{
    private $_imap;
    private $_config;
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        
        $basedir = preg_replace("/tests\/.*/", "", __FILE__);
        PHPFrame::dataDir($basedir."data");
        
        $this->_config = new PHPFrame_Config($basedir."tests".DS."tests.ini");
        
        $this->_imap = new PHPFrame_IMAP(
            $this->_config->get("imap.host"), 
            $this->_config->get("imap.user"), 
            $this->_config->get("imap.pass")
        );
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_getMailboxInfo()
    {
        $array = $this->_imap->getMailboxInfo();
        
        $this->assertArrayHasKey("date", $array);
        $this->assertArrayHasKey("driver", $array);
        $this->assertTrue(in_array($array["driver"], array("pop3", "imap","nntp")));
        $this->assertArrayHasKey("mailbox", $array);
        $this->assertArrayHasKey("nmsgs", $array);
        $this->assertArrayHasKey("recent", $array);
    }
    
    public function test_getMessages()
    {
        $array = $this->_imap->getMessages();
        
        $this->assertType("array", $array);
        $this->assertType("stdClass", $array[0]);
        $this->assertObjectHasAttribute("subject", $array[0]);
        $this->assertObjectHasAttribute("from", $array[0]);
        $this->assertObjectHasAttribute("to", $array[0]);
        $this->assertObjectHasAttribute("date", $array[0]);
        $this->assertObjectHasAttribute("message_id", $array[0]);
        $this->assertObjectHasAttribute("size", $array[0]);
        $this->assertObjectHasAttribute("uid", $array[0]);
        $this->assertObjectHasAttribute("msgno", $array[0]);
        $this->assertObjectHasAttribute("recent", $array[0]);
        $this->assertObjectHasAttribute("flagged", $array[0]);
        $this->assertObjectHasAttribute("answered", $array[0]);
        $this->assertObjectHasAttribute("deleted", $array[0]);
        $this->assertObjectHasAttribute("seen", $array[0]);
        $this->assertObjectHasAttribute("draft", $array[0]);
        $this->assertObjectHasAttribute("body", $array[0]);
        $this->assertType("array", $array[0]->body);
    }
    
//    public function test_constructorException()
//    {
//        $this->setExpectedException("Exception");
//        
//        $imap = new PHPFrame_IMAP(
//            $this->_host, 
//            $this->_user, 
//            $this->_pass
//        );
//    }
    
    public function test_serialise()
    {
        $serialised   = serialize($this->_imap);
        $unserialised = unserialize($serialised);
        
        // We reconnect after first serialisation to have two fully feldged 
        // objects with a valid imap resource
        $this->_imap->reconnect();
        
        $this->assertEquals(serialize($this->_imap), serialize($unserialised));
    }
}
