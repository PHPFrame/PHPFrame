<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ViewHelperTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_helper;

    public function setUp()
    {
        PHPFrame::testMode(true);

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

        $this->_helper = $this->_app->factory()->getViewHelper("cli");
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

    public function test_app()
    {
        $this->assertEquals($this->_app, $this->_helper->app());
    }

    public function test_()
    {
        $this->assertEquals(
            "Some heading\n------------",
            $this->_helper->formatH2("Some heading")
        );
    }
}
