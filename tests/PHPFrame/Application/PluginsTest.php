<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_PluginsTest extends PHPUnit_Framework_TestCase
{
    private $_plugins;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $etc = preg_replace("/tests.*/", "data/CLI_Tool/etc", __FILE__);
        file_put_contents($etc.DS."plugins.xml", "<collection>\n
            <PHPFrame_PluginInfo>\n
                <name>PHPFrame_URLRewriter</name>\n
                <channel />\n
                <summary />\n
                <description />\n
                <author />\n
                <date />\n
                <version></version>\n
                <license></license>\n
                <license_url></license_url>\n
                <enabled>0</enabled>\n
                <id>1</id>\n
                <ctime>1252985443</ctime>\n
                <mtime>1252985443</mtime>\n
                <owner>1</owner>\n
                <group>1</group>\n
                <perms>664</perms>\n
            </PHPFrame_PluginInfo>\n
        </collection>\n
        ");

        $this->_plugins = new PHPFrame_Plugins(
            new PHPFrame_Mapper(
                "PHPFrame_PluginInfo",
                $etc,
                "plugins"
            )
        );

        PHPFrame_Filesystem::rm($etc.DS."plugins.xml");
    }

    public function tearDown()
    {
        //...
    }

    public function test_getIterator()
    {
        $array = iterator_to_array($this->_plugins);

        $this->assertType("array", $array);
        $this->assertTrue(count($array) == 1);
        $this->assertType("PHPFrame_PluginInfo", $array[0]);
    }

    public function test_getInfo()
    {
        $this->assertType(
            "PHPFrame_PluginInfo",
            $this->_plugins->getInfo("PHPFrame_URLRewriter")
        );
    }

    public function test_getInfoFailure()
    {
        $this->setExpectedException("RuntimeException");

        $this->_plugins->getInfo("aaaa");
    }

    public function test_isEnabled()
    {
        $this->assertFalse($this->_plugins->isEnabled("PHPFrame_URLRewriter"));

        $this->_plugins->getInfo("PHPFrame_URLRewriter")->enabled(true);

        $this->assertTrue($this->_plugins->isEnabled("PHPFrame_URLRewriter"));
    }

    public function test_isInstalled()
    {
        $this->assertTrue($this->_plugins->isInstalled("PHPFrame_URLRewriter"));
        $this->assertFalse($this->_plugins->isInstalled("aaaa"));
    }
}
