<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_ImageProcessorTest extends PHPUnit_Framework_TestCase
{
    private $_fixture, $_images;

    public function setUp()
    {
        $this->_fixture = new PHPFrame_ImageProcessor();

        $pattern  = "/(tests\/).*/";
        $replace = "$1data/uploads/";

        $this->_images = array(
            preg_replace($pattern, $replace."Can Maca - Es Cubells 03 Baja.jpg", __FILE__),
            preg_replace($pattern, $replace."Firefox_wallpaper.png", __FILE__),
            preg_replace($pattern, $replace."phd113007s.gif", __FILE__),
            preg_replace($pattern, $replace."photo2.jpg", __FILE__)
        );
    }

    public function tearDown()
    {
        //...
    }

    public function test_resize()
    {
        foreach ($this->_images as $image) {
            $thumb = preg_replace("/\.(jpg|png|gif)/", "-resized.$1", $image);

            if (is_file($thumb)) {
                unlink($thumb);
            }

            $this->assertTrue(!is_file($thumb));

            $dest[] = $thumb;
        }

        $this->_fixture->resize($this->_images, $dest);

        foreach ($dest as $thumb) {
            $this->assertTrue(is_file($thumb));

            if (is_file($thumb)) {
                unlink($thumb);
            }
        }
    }

    public function test_memAllocFailure()
    {
        foreach ($this->_images as $image) {
            $thumb = preg_replace("/\.(jpg|png|gif)/", "-resized.$1", $image);

            if (is_file($thumb)) {
                unlink($thumb);
            }

            $this->assertTrue(!is_file($thumb));

            $dest[] = $thumb;
        }

        $memory_limit = ini_get("memory_limit");
        ini_set("memory_limit", "16M");

        $this->assertFalse($this->_fixture->resize($this->_images, $dest));

        ini_set("memory_limit", $memory_limit);

        $this->assertRegExp(
            "/Image resizing halted to avoid running out of memory\./",
            end($this->_fixture->getMessages())
        );

        foreach ($dest as $thumb) {
            if (is_file($thumb)) {
                unlink($thumb);
            }
        }
    }

    public function test_estimateMemoryAllocation()
    {
        $this->assertTrue($this->_fixture->estimateMemoryAllocation($this->_images) > 0);
    }

    public function test_calculateFudgeFactor()
    {
        $this->assertType("float", $this->_fixture->calculateFudgeFactor($this->_images));
    }
}
