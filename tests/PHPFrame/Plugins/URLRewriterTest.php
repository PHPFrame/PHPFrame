<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_URLRewriterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        PHPFrame::testMode(true);

        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::dataDir($data_dir);

        // Get application install dir (we use CLI Tool for tests)
        $pattern     = '/(.*)(\/|\\\)tests(\/|\\\)PHPFrame(\/|\\\)(.*)/';
        $replacement = '$1$2data$3CLI_Tool';
        $install_dir = preg_replace($pattern, $replacement, __FILE__);

        if (is_dir($install_dir.DS."tmp")) {
            PHPFrame_Filesystem::rm($install_dir.DS."tmp", true);
        }

        // Instantiate application
        $options    = array("install_dir"=>$install_dir);
        $this->_app = new PHPFrame_Application($options);

        $this->_plugin = new PHPFrame_URLRewriter($this->_app);
    }

    public function tearDown()
    {
        // Destroy application
        $this->_app->__destruct();
    }

    public function test_routeStartup()
    {
        // http://localhost/v5/user/index?somevar=1

        $_SERVER['QUERY_STRING'] = "somevar=1";
        $_SERVER['REQUEST_URI'] = "/v5/user/index?somevar=1";

        $request = new PHPFrame_Request();
        $request->requestURI("/v5/user/index?somevar=1");
        $request->scriptName("/v5/index.php");

        $this->assertEquals("", $request->controllerName());
        $this->assertEquals("", $request->action());

        $this->_app->request($request);
        $this->_plugin->routeStartup();

        $this->assertEquals("user", $request->controllerName());
        $this->assertEquals("index", $request->action());
        $this->assertEquals(
            "controller=user&action=index&somevar=1",
            $_SERVER['QUERY_STRING']
        );
        $this->assertEquals(
            "/v5/index.php?controller=user&action=index&somevar=1",
            $_SERVER['REQUEST_URI']
        );
    }

    public function test_postApplyTheme()
    {
        $this->_app->config()->set("base_url", "http://localhost/");
        $response = $this->_app->response();
        $response->body("\"index.php?controller=user&action=index&somevar=1\"");

        $this->_plugin->postApplyTheme();

        $this->assertRegExp("/http:\/\/localhost\/user\/index\?somevar=1/", $response->body());
    }
}
