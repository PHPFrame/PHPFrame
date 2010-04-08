<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ActionControllerTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_controller;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);

        $this->_app = new PHPFrame_Application(
            array("install_dir"=>$install_dir)
        );

        $this->_app->request(new PHPFrame_Request());

        $this->_controller = new PHPFrame_TestableActionController();
    }

    public function tearDown()
    {
        //...
        $tmp_dir = $this->_app->getInstallDir().DS."tmp";
        $app_reg = $tmp_dir.DS."app.reg";

        if (is_file($app_reg)) {
            unlink($app_reg);
        }
        if (is_dir($tmp_dir)) {
            rmdir($tmp_dir);
        }

        $var_dir = $this->_app->getInstallDir().DS."var";
        $app_log = $var_dir.DS."app.log";
        $data_db = $var_dir.DS."data.db";

        if (is_file($app_log)) {
            unlink($app_log);
        }
        if (is_file($data_db)) {
            unlink($data_db);
        }

        // Destroy application
        $this->_app->__destruct();
    }

    public function test_execute()
    {
        $this->assertEquals("", (string) $this->_app->response()->document());
        $this->assertNull($this->_app->request()->action());

        $this->_controller->execute($this->_app);

        $pattern = "/The page title\n==============\n\nLorem ipsum.../";
        $this->assertRegExp($pattern, (string) $this->_app->response()->document());
        $this->assertEquals("index", $this->_app->request()->action());
        $this->assertEquals(200, $this->_app->response()->statusCode());
    }

    public function test_app()
    {
        $this->_controller->execute($this->_app);

        $this->assertType("PHPFrame_Application", $this->_controller->app());
    }

    public function test_config()
    {
        $this->_controller->execute($this->_app);

        $this->assertType("PHPFrame_Config", $this->_controller->config());
    }

    public function test_request()
    {
        $this->_controller->execute($this->_app);

        $this->assertType("PHPFrame_Request", $this->_controller->request());
    }

    public function test_response()
    {
        $this->_controller->execute($this->_app);

        $this->assertType("PHPFrame_Response", $this->_controller->response());
    }

    public function test_registry()
    {
        $this->_controller->execute($this->_app);

        $this->assertType("PHPFrame_Registry", $this->_controller->registry());
    }

    public function test_mailer()
    {
        $this->_controller->execute($this->_app);

        $this->assertNull($this->_controller->mailer());
    }

    public function test_imap()
    {
        $this->_controller->execute($this->_app);

        $this->assertFalse((bool) $this->_app->config()->get("imap.enable"));
        $this->assertNull($this->_controller->imap());
    }

    public function test_logger()
    {
        $this->_controller->execute($this->_app);

        $this->assertType("PHPFrame_Logger", $this->_controller->logger());
    }

    public function test_session()
    {
        $this->_controller->execute($this->_app);

        $this->assertType("PHPFrame_SessionRegistry", $this->_controller->session());
    }

    public function test_user()
    {
        $this->_controller->execute($this->_app);

        $this->assertType("PHPFrame_User", $this->_controller->user());
    }

    public function test_view()
    {

    }

    public function test_helper()
    {

    }

    public function test_cancel()
    {

    }

    public function test_setRedirect()
    {

    }

    public function test_redirect()
    {

    }

    public function test_raiseError()
    {

    }

    public function test_raiseWarning()
    {

    }

    public function test_notifySuccess()
    {

    }

    public function test_notifyInfo()
    {

    }
}
