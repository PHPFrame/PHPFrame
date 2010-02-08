<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_InformerTest extends PHPUnit_Framework_TestCase
{
    private $_informer;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::setDataDir($data_dir);
        
        $this->_informer = new PHPFrame_Informer(
            new PHPFrame_Mailer(), 
            array("lupomontero@gmail.com")
        );
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_serialise()
    {
        $serialised   = serialize($this->_informer);
        $unserialised = unserialize($serialised);
        
        $this->assertEquals($this->_informer, $unserialised);
    }
}
