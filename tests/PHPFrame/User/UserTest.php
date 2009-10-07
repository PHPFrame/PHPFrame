<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_UserTest extends PHPUnit_Framework_TestCase
{
    private $_user;
    
    public function setUp()
    {
        $this->_user = new PHPFrame_User();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_validateAll()
    {
        //$this->_user->validateAll();
    }
    
    public function test_getIterator()
    {
        $array = iterator_to_array($this->_user);
    }
}
