<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_EnumFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;
    
    public function setUp()
    {
        $this->_filter = new PHPFrame_EnumFilter();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_process()
    {
        $enums = array(true, false, 1, -2, 0x29347, 0315, 3.1, -4.1939394944040,
            "test", "193d", "x", array(), array(1, 2), array("test"=>'ok'));
        
        $this->_filter->setOption('enums', $enums);
        $values = array(true,  1, -2, 0x29347, 0315, 3.1, -4.1939394944040,
            "test", "193d", "x", array(), array(1, 2), array("test"=>'ok'));
        
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result === false);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == 0));
        }
    }
    
    public function test_processFailure()
    {
        $enums = array(true, 1, -2, 0x29347, 0315, 3.1, -4.1939394944040,
            "test", "193d", "x", array(), array(1, 2), array("test"=>'ok'));
        
        $values = array(false, 0, 0x1, 'test', array(2), array('something'=>1));
        
        for ($i = 0; $i < count($values); $i++) {
            $result   = $this->_filter->process($values[$i]);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) == $i+1));
        }
    }
}
