<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrameTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //...
    }

    public function tearDown()
    {
        //...
    }

    public function test_construct()
    {
        $refl_obj    = new ReflectionClass("PHPFrame");
        $constructor = $refl_obj->getMethod("__construct");

        // Ensure that constructor is private
        // This class shouldn't be instantiated
        $this->assertTrue($constructor->isPrivate());
    }

    public function test_autoload()
    {
        $this->assertFalse(class_exists("PHPFrame_HTMLUI", false));
        PHPFrame::autoload("PHPFrame_HTMLUI");
        $this->assertTrue(class_exists("PHPFrame_HTMLUI", false));
    }

    public function test_getSession()
    {
        // $this->assertType("PHPFrame_SessionRegistry", PHPFrame::getSession());
    }

    public function test_boot()
    {
        //...
    }

    public function test_testMode()
    {
        // $this->assertFalse(PHPFrame::testMode(false));
        //         $this->assertType("PHPFrame_SessionRegistry", PHPFrame::getSession());

        $this->assertTrue(PHPFrame::testMode(true));
        $this->assertType("PHPFrame_MockSessionRegistry", PHPFrame::getSession());
    }

    public function test_version()
    {
        $this->assertType("string", PHPFrame::version());
    }

    public function test_setDataDirFailure()
    {
        $this->setExpectedException("InvalidArgumentException");

        PHPFrame::dataDir(1);
    }
}
