<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_ArrayTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //...
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_constructFailureBool()
    {
        try {
            $array_obj = new PHPFrame_Array(true);
        } catch (PHPUnit_Framework_Error $e) {
            return;
        }
        
        $this->fail('An expected exception has not been raised.');
    }
    
    public function test_constructFailureInt()
    {
        try {
            $array_obj = new PHPFrame_Array(1);
        } catch (PHPUnit_Framework_Error $e) {
            return;
        }
        
        $this->fail('An expected exception has not been raised.');
    }
    
    public function test_constructFailureFloat()
    {
        try {
            $array_obj = new PHPFrame_Array(1.3);
        } catch (PHPUnit_Framework_Error $e) {
            return;
        }
        
        $this->fail('An expected exception has not been raised.');
    }
    
    public function test_constructFailureString()
    {
        try {
            $array_obj = new PHPFrame_Array("some string");
        } catch (PHPUnit_Framework_Error $e) {
            return;
        }
        
        $this->fail('An expected exception has not been raised.');
    }
    
    public function test_constructFailureObject()
    {
        try {
            $array_obj = new PHPFrame_Array(new stdClass());
        } catch (PHPUnit_Framework_Error $e) {
            return;
        }
        
        $this->fail('An expected exception has not been raised.');
    }
    
    public function test_constructNull()
    {
        $array_obj = new PHPFrame_Array(null);
        $this->assertType("PHPFrame_Array", $array_obj);
    }
    
    public function test_isAssoc()
    {
        $array_obj = new PHPFrame_Array(array(1,2,3));
        $this->assertFalse($array_obj->isAssoc());
        
        $array_obj = new PHPFrame_Array(array("first"=>1, "second"=>2, "third"=>3));
        $this->assertTrue($array_obj->isAssoc());
        
        $assoc_array_obj = new PHPFrame_Array(array("first"=>1,"second"=>2,3, "array"=>array(1,2,3)));
        $this->assertTrue($assoc_array_obj->isAssoc());
    }
    
    public function test_depth()
    {
        $array_obj = new PHPFrame_Array(array(1,2,3));
        $this->assertEquals(1, $array_obj->depth());
        
        $array_obj = new PHPFrame_Array(array("first"=>1, "second"=>2, "third"=>3));
        $this->assertEquals(1, $array_obj->depth());
        
        $assoc_array_obj = new PHPFrame_Array(array("first"=>1,"second"=>2, 3, "array"=>array(1,2,3)));
        $this->assertEquals(2, $assoc_array_obj->depth());
    }
}
