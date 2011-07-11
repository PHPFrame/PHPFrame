<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_SyseventsTest extends PHPUnit_Framework_TestCase
{
    private $_sysevents;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::dataDir($data_dir);

        $this->_sysevents = new PHPFrame_Sysevents();
    }

    public function tearDown()
    {
        //...
    }

    public function test_toString()
    {
        $this->_sysevents->append(
            "Some message...",
            PHPFrame_Subject::EVENT_TYPE_ERROR
        );
        $this->_sysevents->append(
            "Some message...",
            PHPFrame_Subject::EVENT_TYPE_WARNING
        );
        $this->_sysevents->append(
            "Some message...",
            PHPFrame_Subject::EVENT_TYPE_NOTICE
        );
        $this->_sysevents->append(
            "Another message...",
            PHPFrame_Subject::EVENT_TYPE_INFO
        );
        $this->_sysevents->append(
            "Some message...",
            PHPFrame_Subject::EVENT_TYPE_SUCCESS
        );

        $this->assertRegExp(
            "/NOTICE: Some message\.\.\.\nINFO: Another message\.\.\./",
            (string) $this->_sysevents
        );
    }

    public function test_getIterator()
    {
        $this->_sysevents->append(
            "Some message...",
            PHPFrame_Subject::EVENT_TYPE_ERROR
        );
        $this->_sysevents->append(
            "Another message...",
            PHPFrame_Subject::EVENT_TYPE_INFO
        );

        $array = iterator_to_array($this->_sysevents);

        $this->assertInternalType("array", $array);
        $this->assertTrue(count($array) == 2);
        $this->assertInternalType("array", $array[0]);
        $this->assertEquals("Another message...", $array[0][0]);
        $this->assertEquals(PHPFrame_Subject::EVENT_TYPE_INFO, $array[0][1]);
        $this->assertInternalType("array", $array[1]);
        $this->assertEquals("Some message...", $array[1][0]);
        $this->assertEquals(PHPFrame_Subject::EVENT_TYPE_ERROR, $array[1][1]);
    }

    public function test_append()
    {
        $this->_sysevents->append(
            "Some message...",
            PHPFrame_Subject::EVENT_TYPE_INFO
        );

        $this->assertTrue(count($this->_sysevents) == 1);
    }

    public function test_clear()
    {
        $this->_sysevents->append(
            "Some message...",
            PHPFrame_Subject::EVENT_TYPE_INFO
        );
        $this->_sysevents->append(
            "Some message...",
            PHPFrame_Subject::EVENT_TYPE_INFO
        );
        $this->_sysevents->append(
            "Some message...",
            PHPFrame_Subject::EVENT_TYPE_INFO
        );

        $this->assertTrue(count($this->_sysevents) == 3);

        $this->_sysevents->clear();

        $this->assertTrue(count($this->_sysevents) == 0);
    }

    public function test_doUpdate()
    {
        $this->assertTrue(count($this->_sysevents) == 0);

        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);
        $home_dir    = PHPFrame_Filesystem::getUserHomeDir();
        $var_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."var";
        $tmp_dir     = $home_dir.DS.".PHPFrame_CLI_Tool".DS."tmp";

        PHPFrame_Filesystem::ensureWritableDir($home_dir.DS.".PHPFrame_CLI_Tool");

        $app = new PHPFrame_Application(array(
            "install_dir" => $install_dir,
            "var_dir"     => $var_dir,
            "tmp_dir"     => $tmp_dir
        ));

        // Include testable controller
        require_once preg_replace(
            "/Application\/.+/",
            "MVC/ActionControllerTest.php",
            __FILE__
        );

        $subject = new TestableActionController($app);

        $subject->raiseError("some error occurred...");
        $this->_sysevents->update($subject);

        $this->assertTrue(count($this->_sysevents) == 1);

        $app->__destruct();
    }

    public function test_statusCode()
    {
        $this->assertEquals(200, $this->_sysevents->statusCode());
        $this->assertEquals(404, $this->_sysevents->statusCode(404));
        $this->assertEquals(404, $this->_sysevents->statusCode());
        $this->assertEquals(200, $this->_sysevents->statusCode(200));
    }
}
