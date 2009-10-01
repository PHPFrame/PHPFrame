<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_EmailFilterTest extends PHPUnit_Framework_TestCase
{
    private $_filter;
    
    public function setUp()
    {
        $this->_filter = new PHPFrame_EmailFilter();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_process()
    {
        $values = array("me@mydomain.com", "you@example.com", "jon.doe@gmail.com");
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
        $values = array(true, -3, 3.14, "me.domain.com", "you", "me@gmail", array(), new stdClass());
        foreach ($values as $value) {
            $result   = $this->_filter->process($value);
            $messages = $this->_filter->getMessages();
            
            $this->assertFalse($result);
            $this->assertType("bool", $result);
            $this->assertType("array", $messages);
            $this->assertTrue((count($messages) > 0));
        }
    }
}
