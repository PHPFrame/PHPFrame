<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_IntFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;
    
    public function setUp()
    {
        $this->_filter = new PHPFrame_IntFilter();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_process()
    {
        $values = array(1, 182765, -2323, "12", "-34");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertTrue($result !== false);
            $this->assertType("int", $result);
            $this->assertEquals((int) $value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processFailure()
    {
        $values = array(true, false, 0.0, 1.0, 3.14, "some string", array(), new stdClass());
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }
    
    public function test_processRange()
    {
    	$this->_filter->setMinRange(-5);
    	$this->_filter->setMaxRange(10);
        $values = array(1, 2, -5, "8", "-4");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertTrue($result !== false);
            $this->assertType("int", $result);
            $this->assertEquals((int) $value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processRangeFailure()
    {
        $this->_filter->setMinRange(-5);
        $this->_filter->setMaxRange(10);
        $values = array(-6, 254, -35, "11", "-44");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }
    
    public function test_processHex()
    {
    	$this->_filter->setAllowHex(true);
        $values = array("0x00000001", "0xFFFFFF", "0xF0F0F0", "0xAA", "1", 1, -98, 0771);
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertTrue($result !== false);
            $this->assertType("int", $result);
            $this->assertEquals($value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processHexFailure()
    {
        $this->_filter->setAllowHex(false);
        $values = array("0x00000001", "0xFFFFFF", "0xF0F0F0", "0xAA", "a string", array(1,2,3));
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
    
    public function test_processOctal()
    {
        $this->_filter->setAllowOctal(true);
        $values = array("0664", "0771", "0600", "1", 1, -98, 0771);
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertTrue($result !== false);
            $this->assertType("int", $result);
            // equality test commented out because converted integer value will
            // never match its string representation
            //$this->assertEquals($value, (int) $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processOctalFailure()
    {
        $this->_filter->setAllowOctal(false);
        $values = array("0664", "0771", "0600", "a string", array(1,2,3));
        for ($i=0; $i<count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
    
    public function test_processStrictFailure()
    {
        $this->_filter->setStrict(true);
        $values = array(true, false, 3.14, "0", "a string", array(1,2,3), new stdClass());
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
