<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ProfilerTest extends PHPUnit_Framework_TestCase
{
    private $_profiler;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::dataDir($data_dir);

        $this->_profiler = new PHPFrame_Profiler();
    }

    public function tearDown()
    {
        //...
    }

    public function test_toString()
    {
        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);
        $home_dir    = PHPFrame_Filesystem::getUserHomeDir();
        $var_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."var";
        $tmp_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."tmp";

        PHPFrame_Filesystem::ensureWritableDir($home_dir.DS.".PHPFrame_CLI_Tool");

        // Instantiate application
        $app = new PHPFrame_Application(array(
            "install_dir" => $install_dir,
            "var_dir"     => $var_dir,
            "tmp_dir"     => $tmp_dir
        ));

        $app->config()->set("debug.profiler_enable", true);

        $request = new PHPFrame_Request();
        $request->controllerName("man");

        ob_start();
        $app->dispatch($request);
        ob_end_clean();

        $this->assertRegExp("/Total => [\d\.]+ msec/", (string) $app->profiler());

        $app->__destruct();
    }

    public function test_getIterator()
    {
        $this->_profiler->addMilestone();

        $array = iterator_to_array($this->_profiler);
        $this->assertType("array", $array);
    }

    public function test_count()
    {
        $this->assertEquals(1, count($this->_profiler));
    }

    public function test_addMilestone()
    {
        $this->assertNull($this->_profiler->addMilestone());
        $this->assertEquals(2, count($this->_profiler));
    }
}
