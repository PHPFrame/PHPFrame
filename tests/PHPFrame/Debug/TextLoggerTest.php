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
    	
    }
}
