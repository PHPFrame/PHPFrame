<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_ApplicationTest extends PHPUnit_Framework_TestCase
{
    private $_app;
    
    public function setUp()
    {
    	$options = array("install_dir"=>"/home/lupo/Desktop/newapp");
        $this->_app = new PHPFrame_Application($options);
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_a()
    {
        print_r($this->_app);
        exit;
    }
}
