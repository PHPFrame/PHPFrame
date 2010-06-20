<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_InputFilterTest extends PHPUnit_Framework_TestCase
{
    private $_fixture;

    public function setUp()
    {
        $this->_fixture = new PHPFrame_InputFilter();
    }

    public function tearDown()
    {
        //...
    }

    public function test_process()
    {
        $str = "Lorem ipsum... <script>var foo; function bar() { alert('ha!'); }</script>";
        $this->assertEquals("Lorem ipsum...", $this->_fixture->process($str));


        $str = "Lorem ipsum

        <script>
        jQuery('a').click(function() {
            doSomethingNasty();
        });
        </script>";
        $this->assertEquals("Lorem ipsum", $this->_fixture->process($str));

        $str = "Lorem ipsum... <iframe src=\"http://www.google.com\" />";
        $this->assertEquals("Lorem ipsum...", $this->_fixture->process($str));

        $str = "Lorem ipsum... <form action=\"http://www.google.com\"></form>";
        $this->assertEquals("Lorem ipsum... <form></form>", $this->_fixture->process($str));

        $str = "<?php phpinfo(); ?>";
        $this->assertEquals("", $this->_fixture->process($str));
    }
}
