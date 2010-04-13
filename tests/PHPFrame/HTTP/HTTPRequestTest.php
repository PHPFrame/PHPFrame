<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_HTTPRequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //...
    }

    public function tearDown()
    {
        //...
    }

    public function test_handleRedirects()
    {
        $http_request = new PHPFrame_HTTPRequest();

        $this->assertTrue($http_request->handleRedirects());
        $this->assertFalse($http_request->handleRedirects(false));
        $this->assertTrue($http_request->handleRedirects(true));
    }

    public function test_cacheTime()
    {
        $http_request = new PHPFrame_HTTPRequest();

        $this->assertEquals(0, $http_request->cacheTime());
        $this->assertEquals(120, $http_request->cacheTime(120));
        $this->assertEquals(0, $http_request->cacheTime(0));
        $this->assertEquals(0, $http_request->cacheTime(null));
        $this->assertEquals(1, $http_request->cacheTime(true));
        $this->assertEquals(0, $http_request->cacheTime(false));
        $this->assertEquals(3, $http_request->cacheTime(3.14));
        $this->assertEquals(123, $http_request->cacheTime("123"));
        $this->assertEquals(0, $http_request->cacheTime("some string"));
        $this->assertEquals(0, $http_request->cacheTime(array()));
        $this->assertEquals(1, $http_request->cacheTime(array(1,2,3)));
    }

    public function test_cacheDir()
    {
        $http_request = new PHPFrame_HTTPRequest();

        $tmp_dir = PHPFrame_Filesystem::getSystemTempDir();

        $this->assertEquals(null, $http_request->cacheDir());
        $this->assertEquals($tmp_dir, $http_request->cacheDir($tmp_dir));
    }

    public function test_cacheDirFailure()
    {
        $http_request = new PHPFrame_HTTPRequest();

        $this->assertEquals(null, $http_request->cacheDir());

        $this->setExpectedException("RuntimeException");

        $this->assertEquals("/path/to/cache", $http_request->cacheDir("/path/to/cache"));
    }

    public function test_send()
    {
        $http_request  = new PHPFrame_HTTPRequest("http://www.phpframe.org");
        $http_response = $http_request->send();

        $this->assertEquals(200, $http_response->getStatus());
    }

    public function test_sendBadURLFailure()
    {
        $http_request = new PHPFrame_HTTPRequest("sdjncds");

        $this->setExpectedException("RuntimeException");

        $http_request->send();
    }

    public function test_sendNoURLFailure()
    {
        $http_request = new PHPFrame_HTTPRequest();

        $this->setExpectedException("RuntimeException");

        $http_request->send();
    }

    public function test_sendHandleRedirection()
    {
        $http_request  = new PHPFrame_HTTPRequest("http://google.com");
        $http_response = $http_request->send();

        $this->assertEquals(200, $http_response->getStatus());
    }

    public function test_sendDontHandleRedirection()
    {
        $http_request  = new PHPFrame_HTTPRequest("http://google.com");
        $http_request->handleRedirects(false);
        $http_response = $http_request->send();

        $this->assertEquals(301, $http_response->getStatus());
    }

    public function test_sendCacheInWorkingDir()
    {
        $http_request = new PHPFrame_HTTPRequest("http://www.phpframe.org");
        $http_request->cacheTime(60);
        $http_response = $http_request->send();

        $this->assertEquals(200, $http_response->getStatus());

        $http_response2 = $http_request->send();

        $this->assertEquals(200, $http_response->getStatus());
        $this->assertEquals($http_response->getBody(), $http_response2->getBody());

        PHPFrame_Filesystem::rm(getcwd().DS.md5($http_request->getUrl()->getUrl()));
    }

    public function test_sendCacheInGivenDir()
    {
        $tmp_dir = PHPFrame_Filesystem::getSystemTempDir();
        $http_request = new PHPFrame_HTTPRequest("http://www.phpframe.org");
        $http_request->cacheTime(60);
        $http_request->cacheDir($tmp_dir);
        $http_response = $http_request->send();

        $this->assertEquals(200, $http_response->getStatus());

        $http_response2 = $http_request->send();

        $this->assertEquals(200, $http_response->getStatus());
        $this->assertEquals($http_response->getBody(), $http_response2->getBody());

        PHPFrame_Filesystem::rm($tmp_dir.DS.md5($http_request->getUrl()->getUrl()));
    }

    public function test_sendCacheDirNotWritable()
    {

    }

    public function test_sendCacheCorruptedData()
    {
        $tmp_dir = PHPFrame_Filesystem::getSystemTempDir();
        $http_request = new PHPFrame_HTTPRequest("http://www.phpframe.org");
        $http_request->cacheTime(60);
        $http_request->cacheDir($tmp_dir);
        $http_response = $http_request->send();

        $this->assertEquals(200, $http_response->getStatus());

        // Corrupt data!!!
        file_put_contents(
            $tmp_dir.DS.md5($http_request->getUrl()->getUrl()),
            "i am corrupted..."
        );

        // A new request will be sent when the cached data fails to unserialise
        $http_response2 = $http_request->send();

        $this->assertEquals(200, $http_response->getStatus());
        $this->assertEquals($http_response->getBody(), $http_response2->getBody());

        PHPFrame_Filesystem::rm($tmp_dir.DS.md5($http_request->getUrl()->getUrl()));
    }

    public function test_downloadToWorkingDir()
    {
        $http_request = new PHPFrame_HTTPRequest("http://www.phpframe.org");

        $file = getcwd().DS."www.phpframe.org";
        if (is_file($file)) {
            PHPFrame_Filesystem::rm($file);
        }

        ob_start();
        $http_response = $http_request->download();
        ob_end_clean();

        $this->assertEquals(200, $http_response->getStatus());

        $this->assertTrue(is_file($file));

        PHPFrame_Filesystem::rm($file);
    }

    public function test_downloadToWorkingDirWithCustomFilename()
    {
        $http_request = new PHPFrame_HTTPRequest("http://www.phpframe.org");

        $file = getcwd().DS."myfile.html";
        if (is_file($file)) {
            PHPFrame_Filesystem::rm($file);
        }

        ob_start();
        $http_response = $http_request->download(null, "myfile.html");
        ob_end_clean();

        $this->assertEquals(200, $http_response->getStatus());

        $this->assertTrue(is_file($file));

        PHPFrame_Filesystem::rm($file);
    }

    public function test_downloadToGivenDir()
    {
        $http_request = new PHPFrame_HTTPRequest("http://www.phpframe.org");

        $file = PHPFrame_Filesystem::getSystemTempDir().DS."www.phpframe.org";
        if (is_file($file)) {
            PHPFrame_Filesystem::rm($file);
        }

        ob_start();
        $http_response = $http_request->download(
            PHPFrame_Filesystem::getSystemTempDir()
        );
        ob_end_clean();

        $this->assertEquals(200, $http_response->getStatus());

        $this->assertTrue(is_file($file));

        PHPFrame_Filesystem::rm($file);
    }

    public function test_downloadToGivenDirWithCustomFilename()
    {
        $http_request = new PHPFrame_HTTPRequest("http://www.phpframe.org");

        $file = PHPFrame_Filesystem::getSystemTempDir().DS."myfile.html";
        if (is_file($file)) {
            PHPFrame_Filesystem::rm($file);
        }

        ob_start();
        $http_response = $http_request->download(
            PHPFrame_Filesystem::getSystemTempDir(),
            "myfile.html"
        );
        ob_end_clean();

        $this->assertEquals(200, $http_response->getStatus());

        $this->assertTrue(is_file($file));

        PHPFrame_Filesystem::rm($file);
    }
}
