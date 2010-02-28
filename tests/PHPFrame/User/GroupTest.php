<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

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
        $group->name(1234);
        
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
