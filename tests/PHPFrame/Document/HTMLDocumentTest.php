<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_HTMLDocumentTest extends PHPUnit_Framework_TestCase
{
    private $_document;
    
    public function setUp()
    {
        PHPFrame::setTestMode(true);
        
        $this->_document = new PHPFrame_HTMLDocument();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_()
    {
        //print_r($this->_document);
    }
    
    public function test_charset()
    {
        $this->assertEquals("UTF-8", $this->_document->charset());
    }
    
    public function test_mime()
    {
        $this->assertEquals("text/html", $this->_document->mime());
    }
    
    public function test_title()
    {
        $title = "Lorem ipsum";
        $this->_document->title($title);
        
        $this->assertEquals($title, $this->_document->title());
    }
    
    public function test_appendTitle()
    {
        $title = "Lorem ipsum";
        $this->_document->title($title);
        $this->_document->appendTitle($title);
        
        $this->assertEquals($title.$title, $this->_document->title());
    }
    
    public function test_body()
    {
        $body = "<node><nested_node /></node>";
        $this->_document->body($body);
        
        $this->assertXmlStringEqualsXmlString($body, $this->_document->body());
    }
    
    public function test_appendBody()
    {
        $a = "First part";
        $b = "Second part";
        
        $this->_document->body($a);
        $this->_document->appendBody($b);
        
        $this->assertEquals($a.$b, $this->_document->body());
    }
    
    public function test_prependBody()
    {
        $a = "First part";
        $b = "Second part";
        
        $this->_document->body($a);
        $this->_document->prependBody($b);
        
        $this->assertEquals($b.$a, $this->_document->body());
    }
    
    public function test_toString()
    {
        $this->_document->title("The title");
        $this->_document->body("<h1>Hellow World</h1>");
        $str = (string) $this->_document;
        
        // Remove doctype
        $this->assertEquals(1, preg_match("/(.+)(<html.*>.*<\/html>)/s", $str, $matches));
        
        $this->assertType("array", $matches);
        $this->assertEquals(3, count($matches));
        $this->assertEquals(1, preg_match("/DOCTYPE html PUBLIC/", $matches[1]));
        $this->assertEquals(1, preg_match("/<html.*>.*<\/html>/s", $matches[2]));
    }
    
    public function test_dom()
    {
        $this->assertType("DOMDocument", $this->_document->dom());
    }
    
    public function test_doctype()
    {
        $doc_type = $this->_document->doctype();
        $this->assertType("DOMDocumentType", $doc_type);
        $this->assertEquals("html", $doc_type->name);
    }
    
    public function test_addMetaTag()
    {
        $this->_document->addMetaTag("robots", "index, follow");
        
        $pattern = '/<meta content="index, follow" name="robots" \/>/';
        $this->assertRegExp($pattern, (string) $this->_document);
    }
    
    public function test_addScript()
    {
        $url = "http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js";
        $this->_document->addScript($url);
        
        $pattern = '/<script src="http:\/\/ajax\.googleapis\.com\/ajax\/libs\/jquery\/1\.3\.2\/jquery\.min\.js" type="text\/javascript" \/>/';
        $this->assertRegExp($pattern, (string) $this->_document);
    }
    
    public function test_addStyleSheet()
    {
        $href = "http://www.e-noise.com/templates/enoise_iv/css/template.css";
        $this->_document->addStyleSheet($href);
        
        $pattern = '/<link href="http:\/\/www\.e-noise\.com\/templates\/enoise_iv\/css\/template\.css" rel="stylesheet" type="text\/css" \/>/';
        $this->assertRegExp($pattern, (string) $this->_document);
    }
    
    public function test_applyTheme()
    {
        $this->_document->title("My Home Page");
        $this->_document->body("Some really <strong>cool</strong> content...");
        
        $theme_url   = "http://testrunner";
        $theme_path  = dirname(__FILE__).DS."theme.html";
        $install_dir = preg_replace("/tests\/.*/", "data/CLI_Tool", __FILE__);
        $app         = new PHPFrame_Application(array("install_dir"=>$install_dir));
        
        $this->_document->applyTheme($theme_url, $theme_path, $app);
        
        $title = "<title>My Home Page<\/title>";
        $head  = "<head.*>.*$title.*<\/head>";
        
        $sitename = "<h1 id=\"sitename\">\s+<a href=\"index.php\">.*<\/a>\s+<\/h1>";
        $content  = "<div id=\"content\">\s+Some really <strong>cool<\/strong> content...\s+<\/div>";
        $body     = "<body.*>.*$sitename.*$content.*<\/body>";
        
        $html  = "<html.*>\s+$head\s+$body\s+<\/html>";
        
        $pattern = "/".$html."/s";
        
        $this->assertRegExp($pattern, (string) $this->_document);
    }
    
    public function test_bodyOnly()
    {
        $this->_document->title("My Home Page");
        $this->_document->body("Some really <strong>cool</strong> content...");
        $this->_document->bodyOnly(true);
        
        $this->assertEquals($this->_document->body(), (string) $this->_document);
    }
}
