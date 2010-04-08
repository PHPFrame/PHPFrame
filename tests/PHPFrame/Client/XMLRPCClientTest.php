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

    public function test_detectFailure()
    {
        $this->setExpectedException("RuntimeException");

        $_SERVER["CONTENT_TYPE"] = "text/xml";

        PHPFrame_XMLRPCClient::detect();

        unset($_SERVER["CONTENT_TYPE"]);
    }

    public function test_populateRequest()
    {
        $request = new PHPFrame_Request();

        $this->assertType("array", $request->params());
        $this->assertEquals(0, count($request->params()));

        $script_name = $request->scriptName();
        $this->assertTrue(empty($script_name));

        $request_time = $request->requestTime();
        $this->assertTrue(empty($request_time));

        $dom = new DOMDocument();
        $dom->loadXML("<?xml version=\"1.0\"?>
        <methodCall>
           <methodName>dummy.index</methodName>
           <params>
              <param>
                 <value><i4>41</i4></value>
                 </param>
              </params>
           </methodCall>");

        $client = new PHPFrame_XMLRPCClient($dom);

        // Populate the request
        $client->populateRequest($request);

        // Now check that we got some values
        $this->assertEquals("dummy", $request->controllerName());
        $this->assertEquals("index", $request->action());
        $this->assertType("int", $request->requestTime());
   }

    public function test_prepareResponse()
    {
        $response = new PHPFrame_Response();
        $response->document(new PHPFrame_PlainDocument());
        $response->renderer(new PHPFrame_PlainRenderer());

        $this->assertType("PHPFrame_PlainDocument", $response->document());
        $this->assertType("PHPFrame_PlainRenderer", $response->renderer());

        $this->_client->prepareResponse($response, "");

        $this->assertType("PHPFrame_XMLDocument", $response->document());
        $this->assertType("PHPFrame_RPCRenderer", $response->renderer());
    }

    public function test_redirect()
    {
        $this->assertNull($this->_client->redirect("index.php"));
    }
}
