<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_FloatFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;
    
    public function setUp()
    {
        $this->_filter = new PHPFrame_FloatFilter();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_process()
    {
        $values = array(0.0, 1.0, 3.14, 1, 182765, -2323, "12", "-34");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertTrue($result !== false);
            $this->assertType("float", $result);
            $this->assertEquals((float) $value, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processFailure()
    {
        $values = array(true, false, "some string", array(), new stdClass());
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }
    
    public function test_processDecimal()
    {
        $this->_filter->setDecimal(",");
        $values = array("12,67", "-34,5");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertTrue($result !== false);
            $this->assertType("float", $result);
            $this->assertEquals(str_replace(",", ".", $value), $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processDecimalFailure()
    {
        $this->_filter->setDecimal(",");
        $values = array("12.34", "-34.44");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }
    
    public function test_processAllowThousand()
    {
        $this->_filter->setAllowThousand(true);
        $values = array("1,200.50", "-34,000,000.00");
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertTrue($result !== false);
            $this->assertType("float", $result);
            $this->assertEquals(str_replace(",", "", $value), $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processAllowThousandFailure()
    {
        $this->_filter->setAllowThousand(false);
        $values = array("1,200.50", "-34,000,000.00");
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
        $values = array(true, false, 3, -4, "0", "a string", array(1,2,3), new stdClass());
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
