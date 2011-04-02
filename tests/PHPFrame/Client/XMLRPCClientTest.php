<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_XMLRPCClientTest extends PHPUnit_Framework_TestCase
{
    private $_client;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $this->_client = new PHPFrame_XMLRPCClient(new DOMDocument());
    }

    public function tearDown()
    {
        //...
    }

    public function test_serialise()
    {
        $serialised = serialize($this->_client);
        $unserialised = unserialize($serialised);
        $this->assertInstanceOf("PHPFrame_XMLRPCClient", $unserialised);
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

        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REMOTE_ADDR"] = "127.0.0.1";
        $_SERVER["REQUEST_URI"] = "index.php";
        $_SERVER["SCRIPT_NAME"] = "index.php";
        $_SERVER["QUERY_STRING"] = "";
        $_SERVER["REQUEST_TIME"] = time();

        $request->body("<?xml version=\"1.0\"?>
            <methodCall>
                <methodName>app.create</methodName>
                <params>
                    <param>
                        <value><string>MyApp</string></value>
                    </param>
                    <param>
                        <value><string>basic</string></value>
                    </param>
                    <param>
                        <value><boolean>false</boolean></value>
                    </param>
                </params>
            </methodCall>");

        require_once preg_replace(
            "/tests.*/",
            "data/CLI_Tool/src/controllers/app.php",
            __FILE__
        );

        $client = new PHPFrame_XMLRPCClient();

        // Populate the request
        $client->populateRequest($request);

        // Now check that we got some values
        $this->assertEquals("app", $request->controllerName());
        $this->assertEquals("create", $request->action());
        $this->assertInternalType("int", $request->requestTime());

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
        $response->document(new PHPFrame_PlainDocument());
        $response->renderer(new PHPFrame_PlainRenderer());

        $this->assertInstanceOf("PHPFrame_PlainDocument", $response->document());
        $this->assertInstanceOf("PHPFrame_PlainRenderer", $response->renderer());

        $this->_client->prepareResponse($response, "");

        $this->assertInstanceOf("PHPFrame_XMLDocument", $response->document());
        $this->assertInstanceOf("PHPFrame_RPCRenderer", $response->renderer());
    }

    public function test_redirect()
    {
        $this->assertNull($this->_client->redirect("index.php"));
    }
}
