<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_URITest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //...
    }

    public function tearDown()
    {
        //...
    }

    public function test_interface()
    {
        $uri = new PHPFrame_URI("http://someuser:somepass@www.phpframe.org/somedir/somefile.txt?param1=1&param2=hello#top");

        $this->assertEquals("http", $uri->getScheme());
        $this->assertEquals("someuser", $uri->getUser());
        $this->assertEquals("somepass", $uri->getPass());
        $this->assertEquals("www.phpframe.org", $uri->getHost());
        $this->assertEquals("80", $uri->getPort());
        $this->assertEquals("/somedir", $uri->getDirname());
        $this->assertEquals("somefile", $uri->getFilename());
        $this->assertEquals("txt", $uri->getExtension());
        $this->assertEquals(array("param1"=>1, "param2"=>"hello"), $uri->getQuery());
        $this->assertEquals("top", $uri->getFragment());
    }

    public function test_InvalidArgumentException()
    {
        $this->setExpectedException("InvalidArgumentException");

        $uri = new PHPFrame_URI("not a URL");
    }

    public function test_toString()
    {
        $url = "http://www.phpframe.org/index.php?controller=user#fragment";
        $uri = new PHPFrame_URI($url);
        $this->assertEquals($url, (string) $uri);
    }

    public function test_getRequestURICLI()
    {
        $uri = new PHPFrame_URI();
        $this->assertEquals("", (string) $uri);
    }

    public function test_getRequestURIHttpNoPath()
    {
        $_SERVER['HTTP_HOST'] = "localhost";
        $_SERVER['HTTPS'] = "off";
        $_SERVER['SERVER_PORT'] = "80";
        $_SERVER["REQUEST_URI"] = "";

        $uri = new PHPFrame_URI();
        $this->assertRegExp("/http:\/\/localhost\//", (string) $uri);

        unset($_SERVER["HTTP_HOST"]);
        unset($_SERVER["HTTPS"]);
        unset($_SERVER["SERVER_PORT"]);
        unset($_SERVER["REQUEST_URI"]);
    }

    public function test_getRequestURIHttpNoFilename()
    {
        $_SERVER['HTTP_HOST'] = "localhost";
        $_SERVER['HTTPS'] = "off";
        $_SERVER['SERVER_PORT'] = "80";
        $_SERVER["REQUEST_URI"] = "/beta/";

        $uri = new PHPFrame_URI();
        $this->assertRegExp("/http:\/\/localhost\/beta\//", (string) $uri);

        unset($_SERVER["HTTP_HOST"]);
        unset($_SERVER["HTTPS"]);
        unset($_SERVER["SERVER_PORT"]);
        unset($_SERVER["REQUEST_URI"]);
    }

    public function test_getRequestURIHttpPort80()
    {
        $_SERVER['HTTP_HOST'] = "localhost";
        $_SERVER['HTTPS'] = "off";
        $_SERVER['SERVER_PORT'] = "80";
        $_SERVER["REQUEST_URI"] = "/index.php?controller=dummy&action=index";

        $uri = new PHPFrame_URI();
        $this->assertRegExp("/http:\/\/localhost\/index\.php\?controller=dummy/", (string) $uri);

        unset($_SERVER["HTTP_HOST"]);
        unset($_SERVER["HTTPS"]);
        unset($_SERVER["SERVER_PORT"]);
        unset($_SERVER["REQUEST_URI"]);
    }

    public function test_getRequestURIHttpsPort443()
    {
        $_SERVER['HTTP_HOST'] = "localhost";
        $_SERVER['HTTPS'] = "on";
        $_SERVER['SERVER_PORT'] = "443";
        $_SERVER["REQUEST_URI"] = "/index.php?controller=dummy";

        $uri = new PHPFrame_URI();
        $this->assertRegExp("/https:\/\/localhost\/index\.php\?controller=dummy/", (string) $uri);

        unset($_SERVER["HTTP_HOST"]);
        unset($_SERVER["HTTPS"]);
        unset($_SERVER["SERVER_PORT"]);
        unset($_SERVER["REQUEST_URI"]);
    }

    public function test_getRequestURIHttpPort8080()
    {
        $_SERVER['HTTP_HOST'] = "localhost";
        $_SERVER['HTTPS'] = "off";
        $_SERVER['SERVER_PORT'] = "8080";
        $_SERVER["REQUEST_URI"] = "/index.php?controller=dummy";

        $uri = new PHPFrame_URI();
        $this->assertRegExp("/http:\/\/localhost:8080\/index\.php\?controller=dummy/", (string) $uri);

        unset($_SERVER["HTTP_HOST"]);
        unset($_SERVER["HTTPS"]);
        unset($_SERVER["SERVER_PORT"]);
        unset($_SERVER["REQUEST_URI"]);
    }
}
