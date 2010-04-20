<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_DatabaseColumnTest extends PHPUnit_Framework_TestCase
{
    private $_column;

    public function setUp()
    {
        $this->_column = new PHPFrame_DatabaseColumn(array(
            "name"    => "id",
            "type"    => PHPFrame_DatabaseColumn::TYPE_INT,
            "key"     => PHPFrame_DatabaseColumn::KEY_PRIMARY,
            "extra"   => PHPFrame_DatabaseColumn::EXTRA_AUTOINCREMENT,
            "null"    => false,
            "default" => null
        ));
    }

    public function tearDown()
    {
        //...
    }

    public function test_()
    {
        $this->assertType("PHPFrame_DatabaseColumn", $this->_column);
        $this->assertEquals("id", $this->_column->getName());
        $this->assertEquals("int", $this->_column->getType());
        $this->assertEquals("PRI", $this->_column->getKey());
        $this->assertEquals("auto_increment", $this->_column->getExtra());
        $this->assertEquals(false, $this->_column->getNull());
        $this->assertEquals(null, $this->_column->getDefault());
    }

    public function test_setEnums()
    {
        $this->_column = new PHPFrame_DatabaseColumn(array(
            "name"    => "enum_field",
            "type"    => PHPFrame_DatabaseColumn::TYPE_ENUM,
            "null"    => true,
            "default" => null
        ));

        $this->_column->setEnums(array(1,2,3));
    }

    public function test_setEnumsLogicException()
    {
        $this->setExpectedException("LogicException");

        $this->_column->setEnums(array(1,2,3));

        $this->assertType("array", $this->_column->getEnums());
        $this->assertEquals(array(1,2,3), $this->_column->getEnums());
    }
}
