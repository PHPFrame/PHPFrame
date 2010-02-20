<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_RSSDocumentTest extends PHPUnit_Framework_TestCase
{
    private $_rss;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_rss = new PHPFrame_RSSDocument();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_toString()
    {
        $this->_rss->title("My RSS Feed");
        $this->_rss->link("http://www.lupomontero.com/feed");
        $this->_rss->description("Some really cool news feed...");
        $this->_rss->image("http://www.xul.fr/xul.gif", "http://www.xul.fr/en/index.php");
        
        $this->_rss->addItem("Hello world", "http://www.lupomontero.com/hello-world", "Blah blah blah...");
        
        $this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<rss version=\"2.0\">
  <channel>
    <title>My RSS Feed</title>
    <link>http://www.lupomontero.com/feed</link>
    <description>Some really cool news feed...</description>
    <image>
      <url>http://www.xul.fr/xul.gif</url>
      <link>http://www.xul.fr/en/index.php</link>
    </image>
    <item>
      <title>Hello world</title>
      <link>http://www.lupomontero.com/hello-world</link>
      <description>Blah blah blah...</description>
    </item>
  </channel>
</rss>
", (string) $this->_rss);
    }
    
    public function test_link()
    {
        $link = "http://www.lupomontero.com/feed";
        $this->_rss->link($link);
        
        $this->assertEquals($link, $this->_rss->link());
    }
    
    public function test_description()
    {
        $description = "Blah blah blah";
        $this->_rss->description($description);
        
        $this->assertEquals($description, $this->_rss->description());
    }
    
    public function test_image()
    {
        $url  = "http://www.phpframe.org/themes/phpframe.org/images/tree.jpg";
        $link = "http://www.e-noise.com";
        $this->_rss->image($url, $link);
        
        $this->assertEquals(array("url"=>$url, "link"=>$link), $this->_rss->image());
    }
    
    public function test_imageFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $this->_rss->image("Blah");
    }
    
    public function test_items()
    {
        $item = array(
            "title"       => "The item title", 
            "link"        => "http://link/to/the/item", 
            "description" => "A really cool description...", 
            "pub_date"    => "2010-02-20", 
            "author"      => "Lupo Montero"
        );
        
        $this->_rss->items(array($item, $item));
        
        $this->assertEquals(
            array(
                array(
                    "title"       => "The item title", 
                    "link"        => "http://link/to/the/item", 
                    "description" => "A really cool description...", 
                    "pub_date"    => "2010-02-20", 
                    "author"      => "Lupo Montero"
                ),
                array(
                    "title"       => "The item title", 
                    "link"        => "http://link/to/the/item", 
                    "description" => "A really cool description...", 
                    "pub_date"    => "2010-02-20", 
                    "author"      => "Lupo Montero"
                )
            ), 
            $this->_rss->items()
        );
    }
    
    public function test_addItem()
    {
        $this->_rss->addItem(
            "The item title", 
            "http://link/to/the/item", 
            "A really cool description...", 
            "2010-02-20", 
            "Lupo Montero"
        );
        
        $this->assertEquals(
            array(array(
                "title"       => "The item title", 
                "link"        => "http://link/to/the/item", 
                "description" => "A really cool description...", 
                "pub_date"    => "2010-02-20", 
                "author"      => "Lupo Montero"
            )), 
            $this->_rss->items()
        );
    }
}
