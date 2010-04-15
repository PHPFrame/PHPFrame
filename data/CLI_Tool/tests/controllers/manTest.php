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
        $home_dir    = PHPFrame_Filesystem::getUserHomeDir();
        $var_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."var";
        $tmp_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."tmp";

        PHPFrame_Filesystem::ensureWritableDir($home_dir.DS.".PHPFrame_CLI_Tool");

        $this->_app = new PHPFrame_Application(array(
            "install_dir" => $install_dir,
            "var_dir"     => $var_dir,
            "tmp_dir"     => $tmp_dir
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
