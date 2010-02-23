<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_XMLDocumentTest extends PHPUnit_Framework_TestCase
{
    private $_document;
    
    public function setUp()
    {
        PHPFrame::testMode(true);
        
        $this->_document = new PHPFrame_XMLDocument();
    }
    
    public function tearDown()
    {
        //...
    }
    
    public function test_charset()
    {
        $this->assertEquals("UTF-8", $this->_document->charset());
    }
    
    public function test_mime()
    {
        $this->assertEquals("text/xml", $this->_document->mime());
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
    
    public function test_bodyFailure()
    {
        $this->setExpectedException("InvalidArgumentException");
        
        $body = "Lorem ipsum...";
        $this->_document->body($body);
    }
    
    public function test_appendBody()
    {
        $this->setExpectedException("LogicException");
        
        $this->_document->appendBody("");
    }
    
    public function test_prependBody()
    {
        $this->setExpectedException("LogicException");
        
        $this->_document->prependBody("");
    }
    
    public function test_toString()
    {
        $this->_document->title("The title");
        $container = $this->_document->addNode("container", null);
        $this->_document->addNode("item", $container, array("name"=>"item1"));
        $this->_document->addNode("item", $container, null, "Some content");
        $this->_document->addNode("item", $container);
        
        $str = (string) $this->_document;
        $xml  = "<container><item name=\"item1\" /><item>Some content</item>";
        $xml .= "<item /></container>";
        
        $this->assertXmlStringEqualsXmlString($xml, $str);
    }
    
    public function test_dom()
    {
        $this->assertType("DOMDocument", $this->_document->dom());
    }
    
    public function test_addNode()
    {
        $painting = $this->_document->addNode("painting");
        
        $this->_document->addNode("img", $painting, array(
            "src" => "madonna.jpg", 
            "alt" => "Foligno Madonna, by Raphael"
        ));
        
        $xml_content  = "This is Raphael's \"Foligno\" Madonna, painted in ";
        $xml_content .= "<date>1511</date>-<date>1512</date>.";
        $this->_document->addNode("caption", $painting, null, $xml_content);
        
        $this->assertXmlStringEqualsXmlString("<painting>
    <img alt=\"Foligno Madonna, by Raphael\" src=\"madonna.jpg\" />
    <caption>
        This is Raphael&apos;s &quot;Foligno&quot; Madonna, painted in
        <date>1511</date>
        -
        <date>1512</date>
        .
    </caption>
</painting>
", (string) $this->_document);
        
    }
    
    public function test_addNodeAttr()
    {
        $node = $this->_document->addNode("node");
        $this->_document->addNodeAttr($node, "attr", "some value");
        
        
        $this->assertXmlStringEqualsXmlString(
            "<node attr=\"some value\" />", 
            (string) $this->_document
        );
    }
    
    public function test_addNodeContent()
    {
        $node = $this->_document->addNode("node");
        $this->_document->addNodeContent($node, "Some content");
        
        $this->assertXmlStringEqualsXmlString(
            "<node>Some content</node>", 
            (string) $this->_document
        );
    }
    
    public function test_addNodeContentXML()
    {
        $node     = $this->_document->addNode("node");
        $content  = "Some <strong>content</strong> with some ";
        $content .= "<span class=\"keyword\">nodes</span> in it."; 
        
        $this->_document->addNodeContent($node, $content);
        $this->_document->useBeautifier(false);
        
        $this->assertXmlStringEqualsXmlString(
            "<node>".$content."</node>", 
            (string) $this->_document
        );
    }
    
    public function test_addNodeContentXMLFailure()
    {
        $this->setExpectedException("RuntimeException");
        
        $node     = $this->_document->addNode("node");
        $content  = "Some <strong>content</strong> with some ";
        $content .= "<span class=\"keyword\">nodes</spam> in it.";
        
        $this->_document->addNodeContent($node, $content);
    }
    
    public function test_addNodeContentNoXML()
    {
        $node = $this->_document->addNode("node");
        
        $content  = "Some <strong>content</strong> with some ";
        $content .= "<span class=\"keyword\">nodes</spam> in it.";
        
        $this->_document->addNodeContent($node, $content, false);
        
        $xml  = "<node>Some &lt; strong &gt; content &lt; /strong &gt; with ";
        $xml .= "some &lt; span class=\"keyword\" &gt; nodes &lt; /spam &gt; ";
        $xml .= "in it.</node>";
        
        $this->assertXmlStringEqualsXmlString($xml, (string) $this->_document);
    }
}
