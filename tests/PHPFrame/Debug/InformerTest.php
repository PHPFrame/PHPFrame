<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_InformerTest extends PHPUnit_Framework_TestCase
{
    private $_informer;

    public function setUp()
    {
        PHPFrame::testMode(true);

        $data_dir = preg_replace("/tests\/.*/", "data", __FILE__);
        PHPFrame::dataDir($data_dir);

        $this->_informer = new PHPFrame_Informer(
            new PHPFrame_Mailer(),
            array("lupomontero@gmail.com")
        );
    }

    public function tearDown()
    {
        //...
    }

    public function test_doUpdate()
    {
        $mailer = $this->getMock("PHPFrame_Mailer", array("Send"));
        $mailer->expects($this->once())
               ->method("Send");

        $informer = new PHPFrame_Informer(
            $mailer,
            array("lupomontero@gmail.com")
        );

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

        $subject = new ManController($app);
        $subject->attach($informer);
        $subject->notifyEvent("Some event just happened...", 1);

        $app->__destruct();
    }

    public function test_serialise()
    {
        $serialised   = serialize($this->_informer);
        $unserialised = unserialize($serialised);

        $this->assertEquals($this->_informer, $unserialised);
    }
}
