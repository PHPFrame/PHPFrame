<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_CLIClientTest extends PHPUnit_Framework_TestCase
{
    private $_client;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $this->_client = new PHPFrame_CLIClient();
    }

    public function tearDown()
    {
        //...
    }

    public function test_detect()
    {
        $this->assertType("PHPFrame_CLIClient", PHPFrame_CLIClient::detect());
    }

    public function test_detectFailure()
    {
        global $argv;

        // Backup global $argv before we set it to null to pretend this is not
        // a cli request
        $argv_bk = $argv;
        $argv    = null;

        $this->assertFalse(PHPFrame_CLIClient::detect());

        // restore the original value of $argv
        $argv = $argv_bk;
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

        // Backup global $argv and $argc before we set fake values for testing
        global $argv, $argc;
        $argv_bk = $argv;
        $argc_bk = $argc;
        $argv = array(
            "/path/to/bootstrapper",
            "app",
            "create",
            "app_name=MyApp"
        );
        $argc = count($argv);

        // Populate the request
        $this->_client->populateRequest($request);

       // Now check that we got some values
       $this->assertType("array", $request->params());
       $this->assertEquals(2, count($request->params()));

       $script_name = $request->scriptName();
       $this->assertTrue(!empty($script_name));

       $this->assertType("int", $request->requestTime());

       // restore the original value of $argv and $argc
       $argv = $argv_bk;
       $argc = $argc_bk;
   }

    public function test_prepareResponse()
    {
        $response = new PHPFrame_Response();
        $response->document(new PHPFrame_XMLDocument());
        $response->renderer(new PHPFrame_XMLRenderer());

        $this->assertType("PHPFrame_XMLDocument", $response->document());
        $this->assertType("PHPFrame_XMLRenderer", $response->renderer());

        $this->_client->prepareResponse($response, "");

        $this->assertType("PHPFrame_PlainDocument", $response->document());
        $this->assertType("PHPFrame_PlainRenderer", $response->renderer());

    }

    public function test_redirect()
    {
        $this->assertNull($this->_client->redirect("index.php"));
    }
}
