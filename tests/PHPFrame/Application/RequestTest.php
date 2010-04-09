<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_RequestTest extends PHPUnit_Framework_TestCase
{
    private $_request;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $this->_request = new PHPFrame_Request();
    }

    public function tearDown()
    {
        //...
    }

    public function test_toString()
    {
        $this->assertType("string", (string) $this->_request);
    }

    public function test_getIterator()
    {
        $this->assertType("array", iterator_to_array($this->_request));
    }

    public function test_controllerName()
    {
        $this->assertType("string", $this->_request->controllerName("index"));
        $this->assertEquals("index", $this->_request->controllerName());
    }

    public function test_controllerNameException()
    {
        $this->setExpectedException("InvalidArgumentException");

        $this->assertType("string", $this->_request->controllerName("indexJJ"));
    }

    public function test_action()
    {
        $this->assertType("string", $this->_request->action("index"));
        $this->assertEquals("index", $this->_request->action());
    }

    public function test_params()
    {
        $this->assertType("array", $this->_request->params());
    }

    public function test_param()
    {
        $this->assertNull($this->_request->param("aaaaaa"));
        $this->assertType("string", $this->_request->param("myvar", "some value"));
        $this->assertEquals("some value", $this->_request->param("myvar"));
    }

    public function test_headers()
    {
        $this->assertType("array", $this->_request->headers());
    }

    public function test_header()
    {
        $this->assertNull($this->_request->header("aaaaaa"));
        $this->assertType("string", $this->_request->header("Status", 200));
        $this->assertEquals("200", $this->_request->header("Status"));
        $this->assertArrayHasKey("Status", $this->_request->headers());
    }

    public function test_method()
    {
        $this->assertType("string", $this->_request->method("CLI"));
        $this->assertEquals("CLI", $this->_request->method());
    }

    public function test_isPost()
    {
        $this->assertFalse($this->_request->isPost());
    }

    public function test_isGet()
    {
        $this->assertFalse($this->_request->isGet());
    }

    public function test_file()
    {
        $this->assertNull($this->_request->file("aaaa"));

        $this->_request->file(
            "myfile",
            array(
                "name"     => "MyFile.txt",
                "type"     => "text/plain",
                "tmp_name" => "/tmp/php/php1h4j1o",
                "error"    => UPLOAD_ERR_OK,
                "size"     => 12
            )
        );

        $this->assertType("array", $this->_request->file("myfile"));
        $this->assertTrue(count($this->_request->file("myfile")) == 5);

        $this->assertType("array", $this->_request->files());
        $this->assertTrue(count($this->_request->files()) == 1);
    }

    public function test_files()
    {

    }

    public function test_remoteAddr()
    {

    }
    public function test_requestURI()
    {

    }

    public function test_scriptName()
    {

    }

    public function test_queryString()
    {

    }

    public function test_requestTime()
    {

    }

    public function test_outfile()
    {
        $this->assertNull($this->_request->outfile());
        $this->assertEquals("/path/to/file", $this->_request->outfile("/path/to/file"));
        $this->assertEquals("/path/to/file", $this->_request->outfile());
        $this->assertEquals("", $this->_request->outfile(""));
    }

    public function test_quiet()
    {

    }

    public function test_ajax()
    {
        $this->assertFalse($this->_request->ajax());
        $this->assertTrue($this->_request->ajax(true));
        $this->assertTrue($this->_request->ajax());
        $this->assertFalse($this->_request->ajax(false));
    }

    public function test_dispatched()
    {

    }
}
