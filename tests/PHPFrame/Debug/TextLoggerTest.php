<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_TextLoggerTest extends PHPUnit_Framework_TestCase
{
    private $_log_file;
    private $_logger;
    
    public function setUp()
    {
        $this->_log_file = dirname(__FILE__).DS."test.log";
        $this->_logger   = new PHPFrame_TextLogger($this->_log_file);
    }
    
    public function tearDown()
    {
        if (is_file($this->_log_file)) {
            unlink($this->_log_file);
        }
    }
    
    public function test_write()
    {
        $this->_logger->write("Some message");
        
        $log_contents = iterator_to_array($this->_logger);
        
        $this->assertEquals(3, count($log_contents));
        $this->assertEquals(1, preg_match('/Some message$/', $log_contents[2]));
    }
    
    public function test_serialize()
    {
        $serialised   = serialize($this->_logger);
        $unserialised = unserialize($serialised);
        
        $this->assertTrue($this->_logger == $unserialised);
    }
    
    public function test_getAndSetLogLevel()
    {
        $log_level = $this->_logger->getLogLevel();
        
        $this->_logger->setLogLevel(5);
        $this->assertEquals(5, $this->_logger->getLogLevel());
        
        $this->_logger->setLogLevel(1);
        $this->assertEquals(1, $this->_logger->getLogLevel());
    }
    
    public function test_setLogLevelNegativeIntFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_logger->setLogLevel(-1);
    }
    
    public function test_setLogLevelValueTooHighFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_logger->setLogLevel(6);
    }
    
    public function test_setLogLevelBoolTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_logger->setLogLevel(true);
    }
    
    public function test_setLogLevelFloatTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_logger->setLogLevel(3.14);
    }
    
    public function test_setLogLevelStringTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_logger->setLogLevel("some string");
    }
    
    public function test_setLogLevelArrayTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_logger->setLogLevel(array());
    }
    
    public function test_setLogLevelObjectTypeFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_logger->setLogLevel(new stdClass());
    }
}
