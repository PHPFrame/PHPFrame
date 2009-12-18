<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

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
