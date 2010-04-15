<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_PolymorphicPersistentObjectTest extends PHPUnit_Framework_TestCase
{
    private $_obj;

    public function setUp()
    {
        $this->_obj = new MockPolymorphicPersistentObject();
    }

    public function tearDown()
    {
        //...
    }

    public function test_constructEmpty()
    {
        $values = array(null, array());

        foreach ($values as $value) {
            $obj = new MockPolymorphicPersistentObject(array(
                "params" => $value,
                "owner"  => 1,
                "group"  => 1
            ));

            $this->assertNull($obj->params());
            $this->assertTrue($obj->isDirty());
        }
    }

    public function test_constructFailure()
    {
        $values = array(true, false, 1, 123, 3.14, "", "a string", new stdClass());

        $count = 0;
        foreach ($values as $value) {
            try {
                $obj = new MockPolymorphicPersistentObject(array(
                    "params" => false,
                    "owner"  => 1,
                    "group"  => 1
                ));

            } catch (InvalidArgumentException $e) {
                $count++;
            }
        }

        $this->assertEquals(count($values), $count);
    }

    public function test_constructWithParams()
    {
        $obj = new MockPolymorphicPersistentObject(array(
            "params" => array("foo"=>1, "bar"=>2),
            "owner"  => 1,
            "group"  => 1
        ));

        // Param 'bar' should be ignored as it is not defined in param keys
        $this->assertTrue($obj->isDirty());
        $this->assertEquals(1, $obj->param("foo"));
        $this->assertEquals(array("foo"=>1), $obj->params());

        $obj = new ExtendedMockPolymorphicPersistentObject(array(
            "params" => array("foo"=>1, "bar"=>2),
            "owner"  => 1,
            "group"  => 1
        ));

        $this->assertTrue($obj->isDirty());
        $this->assertEquals(1, $obj->param("foo"));
        $this->assertEquals(2, $obj->param("bar"));
        $this->assertEquals(array("foo"=>1, "bar"=>2), $obj->params());
    }

    public function test_getIterator()
    {
        $this->_obj->param("foo", 123);
        $array = iterator_to_array($this->_obj->getIterator());

        $this->assertType("array", $array);
        $this->assertArrayHasKey("type", $array);
        $this->assertArrayHasKey("params", $array);
        $this->assertArrayHasKey("ctime", $array);
        $this->assertArrayHasKey("mtime", $array);
        $this->assertArrayHasKey("owner", $array);
        $this->assertArrayHasKey("group", $array);
        $this->assertArrayHasKey("perms", $array);

        $this->assertEquals("a:1:{s:3:\"foo\";i:123;}", $array["params"]);
    }

    public function test_params()
    {
        $this->assertNull($this->_obj->params());

        $this->_obj->param("foo", 123);

        $params = $this->_obj->params();

        $this->assertType("array", $params);
        $this->assertArrayHasKey("foo", $params);
        $this->assertEquals(123, $params["foo"]);

        $this->_obj->params(array("foo"=>1));

        $this->assertEquals(1, $this->_obj->param("foo"));
    }

    public function test_param()
    {
        $this->assertNull($this->_obj->param("foo"));
        $this->assertEquals(1, $this->_obj->param("foo", 1));
        $this->assertEquals(2, $this->_obj->param("foo", 2));
        $this->assertEquals(2, $this->_obj->param("foo"));
    }

    public function test_getParamKeys()
    {
        $this->assertType("array", $this->_obj->getParamKeys());
    }
}

class MockPolymorphicPersistentObject
    extends PHPFrame_PolymorphicPersistentObject
{
    public function getParamKeys()
    {
        $array = array(
            "foo" => array(
                "def_value"  => null,
                "allow_null" => false,
                "filter"     => new PHPFrame_IntFilter()
            )
        );

        return $array;
    }
}

class ExtendedMockPolymorphicPersistentObject
    extends MockPolymorphicPersistentObject
{
    public function getParamKeys()
    {
        $array = array(
            "bar" => array(
                "def_value"  => null,
                "allow_null" => true,
                "filter"     => new PHPFrame_StringFilter()
            )
        );

        return array_merge(parent::getParamKeys(), $array);
    }
}
