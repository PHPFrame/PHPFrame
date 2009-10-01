<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_GroupTest extends PHPUnit_Framework_TestCase
{
    private $_group;
    
    public function setUp()
    {
        $this->_group = new PHPFrame_Group();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_populate()
    {
    	$group = new PHPFrame_Group();
    	$group->setName(1234);
    	
    	//var_dump(iterator_to_array($group));
    }
    
//    public function test_validateAll()
//    {
//        $this->_group->validateAll();
//    }
    
    public function test_getIterator()
    {
        $array = iterator_to_array($this->_group);
        //print_r($this->_group);
    }
}
