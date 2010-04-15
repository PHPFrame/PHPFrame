<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ControllerDocTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_controller_doc;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::dataDir($data_dir);

        $install_dir = preg_replace("/tests.*/", "data/CLI_Tool", __FILE__);
        $home_dir    = PHPFrame_Filesystem::getUserHomeDir();
        $var_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."var";
        $tmp_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."tmp";

        PHPFrame_Filesystem::ensureWritableDir($home_dir.DS.".PHPFrame_CLI_Tool");

        $this->_app = new PHPFrame_Application(array(
            "install_dir" => $install_dir,
            "var_dir"     => $var_dir,
            "tmp_dir"     => $tmp_dir
        ));

        $this->_controller_doc = new PHPFrame_ControllerDoc("AppController");
    }

    public function tearDown()
    {
        //...
    }

    public function test_toString()
    {
        $this->assertRegExp(
            "/Actions:\s+create\(\\\$app_name, \\\$template, \\\$allow_non_empty_dir, \\\$install_dir\)\s+remove\(\\\$install_dir\)/",
            (string) $this->_controller_doc
        );
    }

    public function test_getActions()
    {
        $actions = $this->_controller_doc->getActions();
        $this->assertType("array", $actions);
        $this->assertTrue(count($actions) > 0);

        foreach ($actions as $action) {
            $this->assertType("PHPFrame_MethodDoc", $action);
        }
    }
}
