<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_URLFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;
    
    public function setUp()
    {
        $this->_filter = new PHPFrame_URLFilter();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_process()
    {
        $values = array(
            "http://www.e-noise.com", 
            "ftp://ftp.example.com", 
            "ssl://www.e-noise.com/clients"
        );
        
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processFailure()
    {
        $values = array(
            true, 
            -3, 
            3.14, 
            "http:/w.cccc.com", 
            "www.e-noise.com", 
            ".com", 
            array(), 
            new stdClass()
        );
        
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
    
    public function test_processPathRequired()
    {
        $this->_filter->setPathRequired(true);
        
        $values = array(
            "http://www.e-noise.com/clients", 
            "http://www.e-noise.com/index.php", 
            "http://www.e-noise.com/", 
            "ftp://www.e-noise.com/downloads/somefile.tgz"
        );
        
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processPathRequiredFailure()
    {
        $this->_filter->setPathRequired(true);
        
        $values = array(
            "http://www.e-noise.com", 
            "http://e-noise.com", 
            "http://localhost", 
            "ftp://www.e-noise.com"
        );
        
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
    
    public function test_processQueryRequired()
    {
        $this->_filter->setQueryRequired(true);
        
        $values = array(
            "http://www.e-noise.com/clients/index.php?controller=dummy", 
            "http://e-noise.com/index.php?id=1&cat=2&section=21", 
            "https://www.e-noise.com/?some_var=something", 
            "http://www.e-noise.com/downloads?file=somefile.tgz"
        );
        
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("string", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processQueryRequiredFailure()
    {
        $this->_filter->setQueryRequired(true);
        
        $values = array(
            "http://www.e-noise.com/clients/index.php/controller=dummy", 
            "http://e-noise.com/index.php&id=1&cat=2&section=21", 
            "https://www.e-noise.com/", 
            "http://www.e-noise.com/downloads",
            "http://www.e-noise.com/index.php"
        );
        
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
}
