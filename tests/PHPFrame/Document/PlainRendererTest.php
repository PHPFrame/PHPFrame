<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_PlainRendererTest extends PHPUnit_Framework_TestCase
{
    private $_renderer;

    public function setUp()
    {
        $this->_renderer = new PHPFrame_PlainRenderer();
    }

    public function tearDown()
    {
        //...
    }

    public function test_render()
    {
        $array = array(
            array(null, null),
            array(true, "1"),
            array(false, ""),
            array(1, "1"),
            array(345, "345"),
            array(-345, "-345"),
            array(3.14, "3.14"),
            array(-3.14, "-3.14"),
            array("some string", "some string")
        );

        foreach ($array as $test_pair) {
            $this->assertEquals($test_pair[1], $this->_renderer->render($test_pair[0]));
        }
    }

    public function test_renderArray()
    {
        $array = array(
            array(array(), "Array\n(\n)\n"),
            array(array(1), "Array\n(\n    [0] => 1\n)\n"),
            array(array(1,2,3), "Array\n(\n    [0] => 1\n    [1] => 2\n    "
                               ."[2] => 3\n)\n")
        );

        foreach ($array as $test_pair) {
            $this->assertEquals($test_pair[1], $this->_renderer->render($test_pair[0]));
        }
    }

    public function test_renderAssoc()
    {
        $array = array(
            array(array("k"=>"v"), "Array\n(\n    [k] => v\n)\n"),
            array(array("k"=>"v", "a"=>new stdClass()), "Array\n(\n    "
                       ."[k] => v\n    [a] => stdClass Object\n        (\n"
                       ."        )\n\n)\n"),
            array(
                array(
                    "key1" => array(1,2, "sfsfaf", array(222, 333, array("k"=>"v"))),
                    "value without a key",
                    "another key" => 3.14
                ),
                "Array\n(\n    [key1] => Array\n        (\n            "
                ."[0] => 1\n            [1] => 2\n            [2] => sfsfaf\n"
                ."            [3] => Array\n                (\n"
                ."                    [0] => 222\n                    "
                ."[1] => 333\n                    [2] => Array\n"
                ."                        (\n                            "
                ."[k] => v\n                        )\n\n                "
                .")\n\n        )\n\n    [0] => value without a key\n    "
                ."[another key] => 3.14\n)\n"
            )

        );

        foreach ($array as $test_pair) {
            $this->assertEquals($test_pair[1], $this->_renderer->render($test_pair[0]));
        }
    }

    public function test_renderTraversable()
    {
        $user = new PHPFrame_User();
        $this->assertEquals(
            "PHPFrame_User\n(\n    [group_id] => 0\n"
           ."    [email] => \n    [password] => \n    [params] => \n"
           ."    [id] => \n    [ctime] => \n    [mtime] => \n"
           ."    [owner] => 0\n    [group] => 0\n    [perms] => 664\n)\n",
            $this->_renderer->render($user)
        );
    }

    public function test_renderObject()
    {
        $obj = new stdClass();
        $obj->somevar = "some value";
        $obj->int = 1234;
        $obj->array = array(1,2,3);

        $this->assertEquals(
            "stdClass\n(\n    [somevar] => some value\n    [int] => 1234\n"
           ."    [array] => Array\n        (\n            [0] => 1\n"
           ."            [1] => 2\n            [2] => 3\n        )\n\n)\n",
            $this->_renderer->render($obj)
        );
    }
}
