<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ClassDocTest extends PHPUnit_Framework_TestCase
{
    private $_class_doc;

    public function setUp()
    {
        $this->_class_doc = new PHPFrame_ClassDoc("PHPFrame_User");
    }

    public function tearDown()
    {
        //...
    }

    public function test_toString()
    {
        $str = (string) $this->_class_doc;
        $this->assertType("string", $str);
        $this->assertRegExp(
            "/Class: PHPFrame_User\n--------------/",
            $str
        );
    }

    public function test_getProperties()
    {
        $this->assertType("array", $this->_class_doc->getProperties());
        $this->assertTrue(count($this->_class_doc->getProperties()) == 2);
    }

    public function test_getPropertiesWithFilter()
    {
        $this->assertTrue(count($this->_class_doc->getProperties(ReflectionProperty::IS_PUBLIC)) == 0);
        $this->assertTrue(count($this->_class_doc->getProperties(ReflectionProperty::IS_PROTECTED)) == 1);
        $this->assertTrue(count($this->_class_doc->getProperties(ReflectionProperty::IS_PRIVATE)) == 1);
        $this->assertTrue(count($this->_class_doc->getProperties(ReflectionProperty::IS_PUBLIC + ReflectionProperty::IS_PROTECTED + ReflectionProperty::IS_PRIVATE)) == 2);
        $this->assertTrue(count($this->_class_doc->getProperties(ReflectionProperty::IS_PUBLIC + ReflectionProperty::IS_PROTECTED)) == 1);
        $this->assertTrue(count($this->_class_doc->getProperties(ReflectionProperty::IS_PROTECTED + ReflectionProperty::IS_PRIVATE)) == 2);
    }

    public function test_getMethods()
    {
        $methods = $this->_class_doc->getMethods();
        $this->assertType("array", $methods);
        $this->assertTrue(count($methods) > 0);
        $this->assertType("PHPFrame_MethodDoc", $methods[0]);
    }

    public function test_getMethodsWithFilter()
    {
        $methods = $this->_class_doc->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $this->assertType("PHPFrame_MethodDoc", $method);
            $this->assertTrue($method->isPublic());
        }

        $methods = $this->_class_doc->getMethods(ReflectionMethod::IS_PROTECTED);

        foreach ($methods as $method) {
            $this->assertType("PHPFrame_MethodDoc", $method);
            $this->assertTrue($method->isProtected());
        }

        $methods = $this->_class_doc->getMethods(ReflectionMethod::IS_PRIVATE);

        foreach ($methods as $method) {
            $this->assertType("PHPFrame_MethodDoc", $method);
            $this->assertTrue($method->isPrivate());
        }

        $methods = $this->_class_doc->getMethods(ReflectionMethod::IS_PROTECTED + ReflectionMethod::IS_PRIVATE);

        foreach ($methods as $method) {
            $this->assertType("PHPFrame_MethodDoc", $method);
            $this->assertTrue($method->isProtected() || $method->isPrivate());
        }
    }
}
