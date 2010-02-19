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
    
    public function test_()
    {
        $this->_rss->title("My RSS Feed");
        $this->_rss->setLink("http://www.lupomontero.com/feed");
        $this->_rss->setDescription("Some really cool news feed...");
        $this->_rss->setImage("http://www.xul.fr/xul.gif", "http://www.xul.fr/en/index.php");
        
        $this->_rss->addItem("Hello world", "http://www.lupomontero.com/hello-world", "Blah blah blah...");
        
//        echo $this->_rss."\n";
//        exit;
    }
}
