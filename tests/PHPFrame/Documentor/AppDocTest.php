<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_AppDocTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_app_doc;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::dataDir($data_dir);

        $install_dir    = preg_replace("/tests.*/", "data/CLI_Tool", __FILE__);
        $home_dir    = PHPFrame_Filesystem::getUserHomeDir();
        $var_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."var";
        $tmp_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."tmp";

        PHPFrame_Filesystem::ensureWritableDir($home_dir.DS.".PHPFrame_CLI_Tool");

        $this->_app = new PHPFrame_Application(array(
            "install_dir" => $install_dir,
            "var_dir"     => $var_dir,
            "tmp_dir"     => $tmp_dir
        ));

        $this->_app_doc = new PHPFrame_AppDoc($install_dir);
    }

    public function tearDown()
    {
        //...
    }

    public function test_toString()
    {
        $this->assertRegExp(
            "/app\s+---\s+Actions:\s+create\(\\\$app_name, \\\$template, \\\$allow_non_empty_dir, \\\$install_dir\)\s+remove\(\\\$install_dir\)/",
            (string) $this->_app_doc
        );
    }

    public function test_getIterator()
    {
        $array = iterator_to_array($this->_app_doc);

        $this->assertType("array", $array);
        $this->assertTrue(count($array) > 0);

        foreach ($array as $key=>$value) {
            $this->assertType("PHPFrame_ControllerDoc", $value);
        }
    }
}
