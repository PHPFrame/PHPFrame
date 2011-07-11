<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_DefaultClientTest extends PHPUnit_Framework_TestCase
{
    private $_client;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $this->_client = new PHPFrame_DefaultClient();
    }

    public function tearDown()
    {
        //...
    }

    public function test_detect()
    {
        $this->assertInstanceOf("PHPFrame_DefaultClient", PHPFrame_DefaultClient::detect());
    }

    public function test_detectFailure()
    {
        //
    }

    public function test_populateRequest()
    {
        $request = new PHPFrame_Request();

        $this->assertInternalType("array", $request->params());
        $this->assertEquals(0, count($request->params()));

        $script_name = $request->scriptName();
        $this->assertTrue(empty($script_name));

        $request_time = $request->requestTime();
        $this->assertTrue(empty($request_time));

        // Populate PHP super globals to fake request
        $_REQUEST["controller"] = "app";
        $_REQUEST["action"] = "create";
        $_REQUEST["app_name"] = "MyApp";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REMOTE_ADDR"] = "127.0.0.1";
        $_SERVER["REQUEST_URI"] = "/";
        $_SERVER["SCRIPT_NAME"] = "/index.php";
        $_SERVER["QUERY_STRING"] = "";
        $_SERVER["REQUEST_TIME"] = "1270658640";

        // Populate the request
        $this->_client->populateRequest($request);

       // Now check that we got some values
       $this->assertInternalType("array", $request->params());
       $this->assertEquals(1, count($request->params()));

       $script_name = $request->scriptName();
       $this->assertTrue(!empty($script_name));
       $this->assertInternalType("int", $request->requestTime());

       // Reset php superglobals used for test
       $_REQUEST = array();
       unset($_SERVER["REQUEST_METHOD"]);
       unset($_SERVER["REMOTE_ADDR"]);
       unset($_SERVER["REQUEST_URI"]);
       unset($_SERVER["SCRIPT_NAME"]);
       unset($_SERVER["QUERY_STRING"]);
       unset($_SERVER["REQUEST_TIME"]);
   }

    public function test_prepareResponse()
    {
        $response = new PHPFrame_Response();
        $response->document(new PHPFrame_XMLDocument());
        $response->renderer(new PHPFrame_XMLRenderer());

        $this->assertInstanceOf("PHPFrame_XMLDocument", $response->document());
        $this->assertInstanceOf("PHPFrame_XMLRenderer", $response->renderer());

        $this->_client->prepareResponse($response, "");

        $this->assertInstanceOf("PHPFrame_HTMLDocument", $response->document());
        $this->assertInstanceOf("PHPFrame_HTMLRenderer", $response->renderer());
    }

    public function test_redirect()
    {
        // ob_start();
        //         $this->assertNull($this->_client->redirect("index.php"));
        //         ob_end_clean();
    }
}
