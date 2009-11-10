<?php
$path_array = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$path_array = array_splice($path_array, 0, (count($path_array)-3));
$PHPFrame   = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
$PHPFrame  .= "src".DIRECTORY_SEPARATOR."PHPFrame.php";
require_once $PHPFrame;

class PHPFrame_URITest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //...
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_Success()
    {
    	$uri = new PHPFrame_URI("http://someuser:somepass@www.phpframe.org/somedir/somefile.txt?param1=1&param2=hello#top");

    	$this->assertEquals("http", $uri->getScheme());
    	$this->assertEquals("someuser", $uri->getUser());    	
    	$this->assertEquals("somepass", $uri->getPass());
    	$this->assertEquals("www.phpframe.org", $uri->getHost());
    	$this->assertEquals("80", $uri->getPort());
    	$this->assertEquals("/somedir", $uri->getDirname());
    	$this->assertEquals("somefile", $uri->getFilename());
    	$this->assertEquals("txt", $uri->getExtension());
    	$this->assertEquals(array("param1"=>1, "param2"=>"hello"), $uri->getQuery());
    	$this->assertEquals("top", $uri->getFragment());
    }
    
    public function test_InvalidArgumentException()
    {
    	$this->setExpectedException("InvalidArgumentException");
    	
        $uri = new PHPFrame_URI("not a URL");
    }
}
