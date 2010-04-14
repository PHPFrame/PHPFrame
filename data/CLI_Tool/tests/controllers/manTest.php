<?php
// Include framework if not inculded yet
require_once preg_replace("/data\/.*/", "src/PHPFrame.php", __FILE__);

class ManControllerTest extends PHPUnit_Framework_TestCase
{
    private $_app;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $install_dir = preg_replace("/tests\/.*/", "", __FILE__);

        $this->_app = new PHPFrame_Application(array(
            "install_dir" => $install_dir
        ));
    }

    public function tearDown()
    {
        // Destroy application
        $this->_app->__destruct();
        
        PHPFrame::getSession()->getSysevents()->clear();
    }

    public function test_index()
    {
        $request = new PHPFrame_Request();
        $request->controllerName("man");
        $request->action("index");

        ob_start();
        $this->_app->dispatch($request);
        ob_end_clean();

        $this->assertRegExp(
            "/PHPFrame Command Line Tool/",
            (string) $this->_app->response()
        );
    }
}
