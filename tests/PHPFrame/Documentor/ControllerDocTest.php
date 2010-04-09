<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ControllerDocTest extends PHPUnit_Framework_TestCase
{
    private $_app, $_controller_doc;

    public function setUp()
    {
        $install_dir = preg_replace("/tests.*/", "data/CLI_Tool", __FILE__);
        $this->_app  = new PHPFrame_Application(array("install_dir"=>$install_dir));

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
