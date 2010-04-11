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

        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);

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

        $_SERVER = array();
    }

    public function test_postDispatch()
    {
        $response = $this->_app->response();
        $base_url = "http://localhost/";
        $this->_app->config()->set("base_url", $base_url);

        $data = array(
            array(
                "index.php",
                $base_url
            ),
            array(
                "index.php?controller=user",
                $base_url."user"
            ),
            array(
                "index.php?controller=user&action=index",
                $base_url."user/index"
            ),
            array(
                "index.php?controller=user&action=index&somevar=1",
                $base_url."user/index?somevar=1"
            ),
            array(
                $base_url,
                $base_url
            ),
            array(
                $base_url."index.php",
                $base_url
            ),
            array(
                $base_url."index.php?controller=user",
                $base_url."user"
            ),
            array(
                $base_url."index.php?controller=user&action=index",
                $base_url."user/index"
            ),
            array(
                $base_url."index.php?controller=user&action=index&somevar=1",
                $base_url."user/index?somevar=1"
            ),
            array(
                "http://google.com/index.php?controller=user",
                "http://google.com/index.php?controller=user"
            ),
            array(
                "http://google.com",
                "http://google.com"
            ),
            array(
                "a string",
                "a string"
            )
        );

        foreach ($data as $item) {
            $response->header("Location", $item[0]);

            $this->_plugin->postDispatch();

            $this->assertEquals($item[1], $response->header("Location"));
            //echo "Expected: ".$item[1]." => Got: ".$response->header("Location")."\n";
        }
    }

    public function test_postApplyTheme()
    {
        $base_url = "http://localhost/";
        $this->_app->config()->set("base_url", $base_url);

        // Create mock body with many different kinds of links
        $body  = "<div>\n";
        $body .= "<a href=\"index.php?controller=user&action=index&somevar=1\">\n";
        $body .= "<a href=\"index.php?controller=user&action=form&id=1\">\n";
        $body .= "<a href=\"index.php?controller=user\">\n";
        $body .= "<a href=\"index.php?controller=user&action=form\">\n";
        $body .= "<a href=\"".$base_url."index.php?controller=user\">\n";
        $body .= "<a href=\"".$base_url."index.php?controller=user&action=form\">\n";
        $body .= "<a href=\"http://notlocalhost/index.php?controller=user\">\n";
        $body .= "<a href=\"http://www.google.com\">\n";
        $body .= "</a></div>\n";

        $this->_app->response()->body($body);

        $this->_plugin->postApplyTheme();

        $this->assertEquals(
            "<div>\n"
            ."<a href=\"http://localhost/user/index?somevar=1\">\n"
            ."<a href=\"http://localhost/user/form?id=1\">\n"
            ."<a href=\"http://localhost/user\">\n"
            ."<a href=\"http://localhost/user/form\">\n"
            ."<a href=\"http://localhost/user\">\n"
            ."<a href=\"http://localhost/user/form\">\n"
            ."<a href=\"http://notlocalhost/index.php?controller=user\">\n"
            ."<a href=\"http://www.google.com\">\n"
            ."</a></div>",
            $this->_app->response()->body()
        );
    }
}
