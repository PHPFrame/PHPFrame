<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ResponseTest extends PHPUnit_Framework_TestCase
{
    private $_response;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $this->_response = new PHPFrame_Response();
    }

    public function tearDown()
    {
        //...
    }

    public function test_construct()
    {
        $response = new PHPFrame_Response(
            new PHPFrame_XMLDocument(),
            new PHPFrame_XMLRenderer()
        );

        $this->assertType("PHPFrame_XMLDocument", $response->document());
        $this->assertType("PHPFrame_XMLRenderer", $response->renderer());
    }

    public function test_toString()
    {
        $this->assertType("string", $this->_response->__toString());
    }

    public function test_statusCode()
    {
        $array = array(200, 301, 302, 303, 400, 401, 403, 404, 500, 501);

        foreach ($array as $code) {
            $this->_response->statusCode($code);
            $this->assertEquals($code, $this->_response->header("Status"));
        }

        // Revert code to its original state (200)
        $this->_response->statusCode(200);
        $this->assertEquals(200, $this->_response->header("Status"));
    }

    public function test_statusCodeFailure()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->_response->statusCode(1);
    }

    public function test_headers()
    {
        // Check the response headers
        $headers = $this->_response->headers();

        $this->assertType("array", $headers);
        $this->assertArrayHasKey("X-Powered-By", $headers);
        $this->assertArrayHasKey("Expires", $headers);
        $this->assertArrayHasKey("Cache-Control", $headers);
        $this->assertArrayHasKey("Pragma", $headers);
        $this->assertArrayHasKey("Status", $headers);
        $this->assertArrayHasKey("Content-Language", $headers);
        $this->assertArrayHasKey("Content-Type", $headers);

        $this->assertEquals(1, preg_match('/PHPFrame/', $headers["X-Powered-By"]));
        $this->assertEquals(200, $headers["Status"]);
    }

    public function test_headerGet()
    {
        $this->assertNull($this->_response->header("aaaaa"));
        $this->assertEquals(1, preg_match('/PHPFrame/', $this->_response->header("X-Powered-By")));
        $this->assertEquals(200, $this->_response->header("Status"));
    }

    public function test_headerSet()
    {
        $this->_response->header("Status", 501);
        $this->assertEquals(501, $this->_response->header("Status"));

        $this->_response->header("Status", 200);
        $this->assertEquals(200, $this->_response->header("Status"));
    }

    public function test_documentGet()
    {
        $this->assertType("PHPFrame_Document", $this->_response->document());
    }

    public function test_documentSet()
    {
        $this->_response->document(new PHPFrame_PlainDocument());
        $this->assertType("PHPFrame_PlainDocument", $this->_response->document());

        $this->_response->document(new PHPFrame_HTMLDocument());
        $this->assertType("PHPFrame_HTMLDocument", $this->_response->document());

        $this->_response->document(new PHPFrame_PlainDocument());
        $this->assertType("PHPFrame_PlainDocument", $this->_response->document());
    }

    public function test_rendererGet()
    {
        $this->assertType("PHPFrame_IRenderer", $this->_response->renderer());
    }

    public function test_rendererSet()
    {
        $this->_response->renderer(new PHPFrame_PlainRenderer());
        $this->assertType("PHPFrame_PlainRenderer", $this->_response->renderer());

        $this->_response->renderer(new PHPFrame_HTMLRenderer("somepath"));
        $this->assertType("PHPFrame_HTMLRenderer", $this->_response->renderer());

        $this->_response->renderer(new PHPFrame_PlainRenderer());
        $this->assertType("PHPFrame_PlainRenderer", $this->_response->renderer());
    }

    public function test_body()
    {
        $this->assertEquals("", $this->_response->body());

        $this->_response->body("some content...");

        $this->assertEquals("some content...", $this->_response->body());

        $this->_response->body("\nsome more content...", false, true);

        $this->assertEquals("some content...\nsome more content...", $this->_response->body());
    }

    public function test_send()
    {

    }
}
