<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_JSONRendererTest extends PHPUnit_Framework_TestCase
{
    private $_renderer;

    public function setUp()
    {
        $this->_renderer = new PHPFrame_JSONRenderer(false);
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
            array("some string", "\"some string\"")
        );

        foreach ($array as $test_pair) {
            $this->assertEquals($test_pair[1], $this->_renderer->render($test_pair[0]));
        }
    }

    public function test_renderArray()
    {
        $array = array(
            array(array(), "[]"),
            array(array(1), "[\n    1\n]"),
            array(array(1,2,3), "[\n    1,\n    2,\n    3\n]")
        );

        foreach ($array as $test_pair) {
            $this->assertEquals($test_pair[1], $this->_renderer->render($test_pair[0]));
        }
    }

    public function test_renderAssoc()
    {
        $array = array(
            array(array("k"=>"v"), "{\n    \"k\": \"v\"\n}"),
            array(array("k"=>"v", "a"=>new stdClass()), "{\n    \"k\": \"v\",\n    \"a\": {\n        \"stdClass\": []\n    }\n}"),
            array(
                array(
                    "key1" => array(1,2, "sfsfaf", array(222, 333, array("k"=>"v"))),
                    "value without a key",
                    "another key" => 3.14
                ),
                "{
    \"key1\": [
        1,
        2,
        \"sfsfaf\",
        [
            222,
            333,
            {
                \"k\": \"v\"
            }
        ]
    ],
    \"0\": \"value without a key\",
    \"another key\": 3.14
}"
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
            "{
    \"group_id\": 0,
    \"email\": ,
    \"password\": ,
    \"params\": \"\",
    \"id\": ,
    \"ctime\": ,
    \"mtime\": ,
    \"owner\": 0,
    \"group\": 0,
    \"perms\": 664
}",
            $this->_renderer->render($user)
        );
    }

    public function test_renderObject()
    {
        $obj = new stdClass();
        $obj->somevar = "some value";
        $obj->int = 1234;
        $obj->array = array(1,2,3);

        //print_r($this->_renderer->render($obj));
    }

    public function test_renderWithPhpJson()
    {
        $array = array(
            array(array(), "[]"),
            array(array(1), "[1]"),
            array(array(1,2,3), "[1,2,3]")
        );

        $this->_renderer->usePhpJson(true);

        foreach ($array as $test_pair) {
            $this->assertEquals($test_pair[1], $this->_renderer->render($test_pair[0]));
        }
    }
}
