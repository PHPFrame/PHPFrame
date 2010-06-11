<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ActionControllerTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_controller;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::dataDir($data_dir);

        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);
        $home_dir    = PHPFrame_Filesystem::getUserHomeDir();
        $var_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."var";
        $tmp_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."tmp";

        PHPFrame_Filesystem::ensureWritableDir($home_dir.DS.".PHPFrame_CLI_Tool");

        $this->_app = new PHPFrame_Application(array(
            "install_dir" => $install_dir,
            "var_dir"     => $var_dir,
            "tmp_dir"     => $tmp_dir
        ));

        $this->_app->request(new PHPFrame_Request());

        $this->_controller = new TestableActionController($this->_app);
    }

    public function tearDown()
    {
        //...
        $tmp_dir = $this->_app->getTmpDir();
        $app_reg = $tmp_dir.DS."app.reg";

        if (is_file($app_reg)) {
            unlink($app_reg);
        }
        if (is_dir($tmp_dir)) {
            rmdir($tmp_dir);
        }

        $var_dir = $this->_app->getVarDir();
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

        $this->_controller->execute();

        $pattern = "/The page title\n==============\n\nLorem ipsum.../";
        $this->assertRegExp($pattern, (string) $this->_app->response()->document());
        $this->assertEquals("index", $this->_app->request()->action());
        $this->assertEquals(200, $this->_app->response()->statusCode());
    }

    public function test_doRedirect()
    {
        $request = $this->_app->request();
        $request->controllerName("testableaction");
        $request->action("doRedirect");

        ob_start();
        $this->_app->dispatch($request);
        $output = ob_get_contents();
        ob_end_clean();

        $pattern = "/The page title\n==============\n\nLorem ipsum.../";
        $this->assertRegExp($pattern, $output);
    }

    public function test_executeUnknownActionFailure()
    {
        $this->_app->request()->action("aaa");

        $this->setExpectedException("BadMethodCallException");

        $this->_controller->execute();
    }

    public function test_executeProtectedActionFailure()
    {
        $this->_app->request()->action("config");

        $this->setExpectedException("LogicException");

        $this->_app->dispatch();
    }

    public function test_app()
    {
        $this->_controller->execute();

        $this->assertType("PHPFrame_Application", $this->_controller->app());
    }

    public function test_config()
    {
        $this->_controller->execute();

        $this->assertType("PHPFrame_Config", $this->_controller->config());
    }

    public function test_request()
    {
        $this->_controller->execute();

        $this->assertType("PHPFrame_Request", $this->_controller->request());
    }

    public function test_response()
    {
        $this->_controller->execute();

        $this->assertType("PHPFrame_Response", $this->_controller->response());
    }

    public function test_registry()
    {
        $this->_controller->execute();

        $this->assertType("PHPFrame_Registry", $this->_controller->registry());
    }

    public function test_mailer()
    {
        $this->_controller->execute();

        $this->assertNull($this->_controller->mailer());
    }

    public function test_imap()
    {
        $this->_controller->execute();

        $this->assertFalse((bool) $this->_app->config()->get("imap.enable"));
        $this->assertNull($this->_controller->imap());
    }

    public function test_logger()
    {
        $this->_controller->execute();

        $this->assertType("PHPFrame_Logger", $this->_controller->logger());
    }

    public function test_session()
    {
        $this->_controller->execute();

        $this->assertType("PHPFrame_SessionRegistry", $this->_controller->session());
    }

    public function test_user()
    {
        $this->_controller->execute();

        $this->assertType("PHPFrame_User", $this->_controller->user());
    }

    public function test_view()
    {

    }

    public function test_helper()
    {

    }

    public function test_setRedirect()
    {
        $this->_controller->execute();

        $redirect_url = "index.php?controller=dummy&action=index&param1=1";
        $this->_controller->setRedirect($redirect_url);

        $this->assertEquals(303, $this->_app->response()->statusCode());
        $this->assertEquals($redirect_url, $this->_app->response()->header("Location"));
    }

    public function test_raiseError()
    {
        $this->_controller->attach($this->_app->session()->getSysevents());

        $this->_controller->raiseError("I am an error message");

        $array = iterator_to_array($this->_app->session()->getSysevents());
        $this->assertTrue(count($array) == 1);
        $this->assertType("array", $array[0]);
        $this->assertEquals("I am an error message", $array[0][0]);
        $this->assertEquals(PHPFrame_Subject::EVENT_TYPE_ERROR, $array[0][1]);

        $this->_app->session()->getSysevents()->clear();
    }

    public function test_raiseWarning()
    {
        $this->_controller->attach($this->_app->session()->getSysevents());

        $this->_controller->raiseWarning("I am a warning");

        $array = iterator_to_array($this->_app->session()->getSysevents());
        $this->assertTrue(count($array) == 1);
        $this->assertType("array", $array[0]);
        $this->assertEquals("I am a warning", $array[0][0]);
        $this->assertEquals(PHPFrame_Subject::EVENT_TYPE_WARNING, $array[0][1]);

        $this->_app->session()->getSysevents()->clear();
    }

    public function test_notifySuccess()
    {
        $this->_controller->attach($this->_app->session()->getSysevents());

        $this->_controller->notifySuccess("Yay!");

        $array = iterator_to_array($this->_app->session()->getSysevents());
        $this->assertTrue(count($array) == 1);
        $this->assertType("array", $array[0]);
        $this->assertEquals("Yay!", $array[0][0]);
        $this->assertEquals(PHPFrame_Subject::EVENT_TYPE_SUCCESS, $array[0][1]);

        $this->_app->session()->getSysevents()->clear();
    }

    public function test_notifyInfo()
    {
        $this->_controller->attach($this->_app->session()->getSysevents());

        $this->_controller->notifyInfo("Some info...");

        $array = iterator_to_array($this->_app->session()->getSysevents());
        $this->assertTrue(count($array) == 1);
        $this->assertType("array", $array[0]);
        $this->assertEquals("Some info...", $array[0][0]);
        $this->assertEquals(PHPFrame_Subject::EVENT_TYPE_INFO, $array[0][1]);

        $this->_app->session()->getSysevents()->clear();
    }
}

class TestableActionController extends PHPFrame_ActionController
{
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app, "index");
    }

    public function index()
    {
        $this->response()->title("The page title");
        $this->response()->body("Lorem ipsum...");
    }

    public function doRedirect()
    {
        $this->request()->controllerName("user");
        $this->request()->action("index");
        $this->request()->dispatched(false);
    }

    public function app()
    {
        return parent::app();
    }

    public function config()
    {
        return parent::config();
    }

    public function request()
    {
        return parent::request();
    }

    public function response()
    {
        return parent::response();
    }

    public function registry()
    {
        return parent::registry();
    }

    public function mailer()
    {
        return parent::mailer();
    }

    public function imap()
    {
        return parent::imap();
    }

    public function logger()
    {
        return parent::logger();
    }

    public function session()
    {
        return parent::session();
    }

    public function user()
    {
        return parent::user();
    }

    public function raiseError($msg)
    {
        parent::raiseError($msg);
    }

    public function raiseWarning($msg)
    {
        parent::raiseWarning($msg);
    }

    public function notifyInfo($msg)
    {
        parent::notifyInfo($msg);
    }

    public function notifySuccess($msg)
    {
        parent::notifySuccess($msg);
    }
}
