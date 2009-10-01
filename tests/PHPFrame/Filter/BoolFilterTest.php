<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_BoolFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;
    
    public function setUp()
    {
        $this->_filter = new PHPFrame_BoolFilter();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_process()
    {
    	$true_values = array(true, 1, 0x00000001, "1", "true", "on", "yes");
    	foreach ($true_values as $true_value) {
    	    $result   = $this->_filter->process($true_value);
	        $messages = $this->_filter->getMessages();
	        
	        $this->assertFalse($result === false);
	        $this->assertType("bool", $result);
	        $this->assertEquals(true, $result);
	        $this->assertType("array", $messages);
	        $this->assertTrue((count($messages) == 0));
    	}
    	
        $false_values = array(false, 0, "0", "false", "off", "no", "");
        foreach ($false_values as $false_value) {
            $result   = $this->_filter->process($false_value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse(is_null($result));
            $this->assertType("bool", $result);
            $this->assertEquals(false, $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processFailure()
    {
        $bad_values = array(3, 200, -2.3, -2, 0664, 0.0, 1.0, 3.14, array(), new stdClass());
        foreach ($bad_values as $bad_value) {
            $result   = $this->_filter->process($bad_value);
            $messages = $this->_filter->getMessages();
            
            $this->assertTrue(is_null($result));
            $this->assertType("null", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }
    
    public function test_processStrictFailure()
    {
    	$this->_filter->setStrict(true);
        $bad_values = array(1, 123, 3.14, "0", "true", "a string", array(1,2,3), new stdClass());
        for ($i=0; $i<count($bad_values); $i++) {
            $result   = $this->_filter->process($bad_values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertTrue(is_null($result));
            $this->assertType("null", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == ($i+1)));
        }
    }
}
