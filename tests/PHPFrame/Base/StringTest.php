<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_StringTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //...
    }

    public function tearDown()
    {
        //...
    }

    public function test_len()
    {
        $str = new PHPFrame_String("some string");
        $this->assertEquals(11, $str->len());
    }

    public function test_upper()
    {
        $str = new PHPFrame_String("some string");
        $this->assertEquals("SOME STRING", $str->upper());
    }

    public function test_lower()
    {
        $str = new PHPFrame_String("SOME STRING");
        $this->assertEquals("some string", $str->lower());
    }

    public function test_upperFirst()
    {
        $str = new PHPFrame_String("some string");
        $this->assertEquals("Some string", $str->upperFirst());

        $str = new PHPFrame_String("SOME STRING");
        $this->assertEquals("Some string", $str->upperFirst());

        $str = new PHPFrame_String("somE StrING");
        $this->assertEquals("Some string", $str->upperFirst());
    }

    public function test_upperWords()
    {
        $str = new PHPFrame_String("some string");
        $this->assertEquals("Some String", $str->upperWords());

        $str = new PHPFrame_String("SOME STRING");
        $this->assertEquals("Some String", $str->upperWords());

        $str = new PHPFrame_String("somE StrING");
        $this->assertEquals("Some String", $str->upperWords());
    }

    public function test_html()
    {

    }

    public function test_limitChars()
    {
        $str = new PHPFrame_String("some very long string");
        $this->assertEquals("some ve...", $str->limitChars(10));

        $str = new PHPFrame_String("some very long string");
        $this->assertEquals("some very ", $str->limitChars(10, false));

        $str = new PHPFrame_String("some very long string");
        $this->assertEquals("some very long string", $str->limitChars(21));
    }

    public function test_limitWords()
    {
        $str = new PHPFrame_String("some very long string");
        $this->assertEquals("some very ...", $str->limitWords(10));

        $str = new PHPFrame_String("some very long string");
        $this->assertEquals("some very long ...", $str->limitWords(15));

        $str = new PHPFrame_String("some very long string");
        $this->assertEquals("some very", $str->limitWords(10, false));
    }

    public function test_fixLength()
    {
        $str = new PHPFrame_String("some very long string");
        $this->assertEquals("some ve...", $str->fixLength(10));

        $str = new PHPFrame_String("some very long string");
        $this->assertEquals("some very ", $str->fixLength(10, false));

        $str = new PHPFrame_String("a string");
        $this->assertEquals("a string  ", $str->fixLength(10));
    }
}
