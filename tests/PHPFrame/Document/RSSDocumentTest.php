<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_RSSDocumentTest extends PHPUnit_Framework_TestCase
{
    private $_rss;

    public function setUp()
    {
        PHPFrame::testMode(true);

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

        $this->_rss->addItem(
            "Hello world",
            "http://www.lupomontero.com/hello-world",
            "Blah blah blah...",
            "2010-02-20",
            "Lupo Montero"
        );

        $expected = "<?xml version=\"1.0\"?>\n"
        ."<rss version=\"2.0\"><channel><title>My RSS Feed</title><link>"
        ."http://www.lupomontero.com/feed</link><description>Some really cool "
        ."news feed...</description><image><url>http://www.xul.fr/xul.gif"
        ."</url><link>http://www.xul.fr/en/index.php</link></image><item>"
        ."<title>Hello world</title><link>http://www.lupomontero.com/hello-"
        ."world</link><description>Blah blah blah...</description><pubDate>"
        ."2010-02-20</pubDate><author>Lupo Montero</author></item></channel>"
        ."</rss>\n";

        $this->assertEquals($expected, (string) $this->_rss);
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

    public function test_loadAtom()
    {
        $str = '<?xml version="1.0" encoding="UTF-8"?>
        <feed xml:lang="en-US" xmlns="http://www.w3.org/2005/Atom">
          <id>tag:github.com,2008:/PHPFrame/PHPFrame/commits/master</id>
          <link type="text/html" href="http://github.com/PHPFrame/PHPFrame/commits/master/" rel="alternate"/>
          <link type="application/atom+xml" href="http://github.com/PHPFrame/PHPFrame/commits/master.atom" rel="self"/>
          <title>Recent Commits to PHPFrame:master</title>
          <updated>2010-04-19T12:53:40-07:00</updated>
          <entry>
            <id>tag:github.com,2008:Grit::Commit/6c0fbb8aa25890ddbf114a451e32dd3dadd5b6aa</id>
            <link type="text/html" href="http://github.com/PHPFrame/PHPFrame/commit/6c0fbb8aa25890ddbf114a451e32dd3dadd5b6aa" rel="alternate"/>
            <title>Fixed minor bug in DateFilter class.</title>
            <updated>2010-04-19T12:53:40-07:00</updated>
            <content type="html">&lt;pre&gt;m src/PHPFrame/Filter/DateFilter.php

        Fixed minor bug in DateFilter class.&lt;/pre&gt;</content>
            <author>
              <name>Lupo Montero</name>
            </author>
          </entry>
          <entry>
            <id>tag:github.com,2008:Grit::Commit/40595ea0931542e88fc6250cb4938a9b4d48cdf5</id>
            <link type="text/html" href="http://github.com/PHPFrame/PHPFrame/commit/40595ea0931542e88fc6250cb4938a9b4d48cdf5" rel="alternate"/>
            <title>Standarising whitespace...</title>
            <updated>2010-04-19T12:42:10-07:00</updated>
            <content type="html">&lt;pre&gt;m LICENSE
        m build/makedoc.xml

        Standarising whitespace...&lt;/pre&gt;</content>
            <author>
              <name>Lupo Montero</name>
            </author>
          </entry>
          <entry>
            <id>tag:github.com,2008:Grit::Commit/fea39f1aea4f6e2c08ffb564c04f0b91c4046dee</id>
            <link type="text/html" href="http://github.com/PHPFrame/PHPFrame/commit/fea39f1aea4f6e2c08ffb564c04f0b91c4046dee" rel="alternate"/>
            <title>Updated CLIs action controller template class with new constructor signature</title>
            <updated>2010-04-19T05:12:59-07:00</updated>
            <content type="html">&lt;pre&gt;m data/CLI_Tool/data/class-templates/ActionController.php

        Updated CLIs action controller template class with new constructor signature&lt;/pre&gt;</content>
            <author>
              <name>Lupo Montero</name>
            </author>
          </entry>
          <entry>
            <id>tag:github.com,2008:Grit::Commit/bad31217ffaaf1c27cd59617985935e842db90d7</id>
            <link type="text/html" href="http://github.com/PHPFrame/PHPFrame/commit/bad31217ffaaf1c27cd59617985935e842db90d7" rel="alternate"/>
            <title>Fixed order in which obesrvers are attached to exception handler in application class</title>
            <updated>2010-04-19T05:12:07-07:00</updated>
            <content type="html">&lt;pre&gt;m src/PHPFrame/Application/Application.php
        m tests/PHPFrame/Application/ApplicationTest.php

        Fixed order in which obesrvers are attached to exception handler in application class&lt;/pre&gt;</content>
            <author>
              <name>Lupo Montero</name>
            </author>
          </entry>
          <entry>
            <id>tag:github.com,2008:Grit::Commit/fd7aa55c33b30804ecc236ffaf54cb3e3c28b077</id>
            <link type="text/html" href="http://github.com/PHPFrame/PHPFrame/commit/fd7aa55c33b30804ecc236ffaf54cb3e3c28b077" rel="alternate"/>
            <title>Changed ActionController constructor to take instance of app as argument to increase flexibility in controllers. This change will break the API si please check the mailing list for a topic I will post explaining how to change your action controllers after you update to 1.0 build.113 or higher.</title>
            <updated>2010-04-17T08:48:49-07:00</updated>
            <content type="html">&lt;pre&gt;m README.md
        m build/build.xml
        m data/CLI_Tool/src/controllers/app.php
        m data/CLI_Tool/src/controllers/config.php
        m data/CLI_Tool/src/controllers/man.php
        m data/CLI_Tool/src/controllers/scaffold.php
        m data/CLI_Tool/src/models/apptemplate.php
        m src/PHPFrame/Application/Application.php
        m src/PHPFrame/MVC/ActionController.php
        m src/PHPFrame/MVC/MVCFactory.php
        m tests/PHPFrame/Application/SyseventsTest.php
        m tests/PHPFrame/Debug/InformerTest.php
        m tests/PHPFrame/MVC/ActionControllerTest.php

        Changed ActionController constructor to take instance of app as argument to increase flexibility in controllers. This change will break the API si please check the mailing list for a topic I will post explaining how to change your action controllers after you update to 1.0 build.113 or higher.&lt;/pre&gt;</content>
            <author>
              <name>Lupo Montero</name>
            </author>
          </entry>
        </feed>
        ';

        $this->_rss->loadXML($str);

        $this->assertEquals(
            "http://github.com/PHPFrame/PHPFrame/commits/master.atom",
            $this->_rss->link()
        );
        $this->assertEquals(5, count($this->_rss->items()));
    }

    public function test_loadRSS2()
    {
        $str = '<?xml version="1.0" encoding="UTF-8"?>
        <rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" xmlns:georss="http://www.georss.org/georss">
          <channel>
            <title>Twitter / PHPFrame</title>
            <link>http://twitter.com/PHPFrame</link>
            <atom:link type="application/rss+xml" href="http://twitter.com/statuses/user_timeline/69732903.rss" rel="self"/>
            <description>Twitter updates from PHPFrame / PHPFrame.</description>
            <language>en-us</language>
            <ttl>40</ttl>
          <item>
            <title>PHPFrame: [PHPFrame] http://bit.ly/cE39PC Lupo Montero - 1 commits</title>
            <description>PHPFrame: [PHPFrame] http://bit.ly/cE39PC Lupo Montero - 1 commits</description>
            <pubDate>Mon, 19 Apr 2010 19:44:33 +0000</pubDate>
            <guid>http://twitter.com/PHPFrame/statuses/12472512646</guid>
            <link>http://twitter.com/PHPFrame/statuses/12472512646</link>
          </item>
          <item>
            <title>PHPFrame: [PHPFrame] http://bit.ly/cE39PC Lupo Montero - 2 commits</title>
            <description>PHPFrame: [PHPFrame] http://bit.ly/cE39PC Lupo Montero - 2 commits</description>
            <pubDate>Mon, 19 Apr 2010 12:15:21 +0000</pubDate>
            <guid>http://twitter.com/PHPFrame/statuses/12452339459</guid>
            <link>http://twitter.com/PHPFrame/statuses/12452339459</link>
          </item>
          <item>
            <title>PHPFrame: [PHPFrame] http://bit.ly/cE39PC Lupo Montero - 1 commits</title>
            <description>PHPFrame: [PHPFrame] http://bit.ly/cE39PC Lupo Montero - 1 commits</description>
            <pubDate>Sat, 17 Apr 2010 15:54:04 +0000</pubDate>
            <guid>http://twitter.com/PHPFrame/statuses/12347889736</guid>
            <link>http://twitter.com/PHPFrame/statuses/12347889736</link>
          </item>
          <item>
            <title>PHPFrame: [PHPFrame] http://bit.ly/cE39PC Lupo Montero - 2 commits</title>
            <description>PHPFrame: [PHPFrame] http://bit.ly/cE39PC Lupo Montero - 2 commits</description>
            <pubDate>Fri, 16 Apr 2010 20:06:49 +0000</pubDate>
            <guid>http://twitter.com/PHPFrame/statuses/12301989398</guid>
            <link>http://twitter.com/PHPFrame/statuses/12301989398</link>
          </item>
          <item>
            <title>PHPFrame: [PHPFrame] http://bit.ly/cK7xwz Lupo Montero - 1 commits</title>
            <description>PHPFrame: [PHPFrame] http://bit.ly/cK7xwz Lupo Montero - 1 commits</description>
            <pubDate>Fri, 16 Apr 2010 16:51:39 +0000</pubDate>
            <guid>http://twitter.com/PHPFrame/statuses/12293177832</guid>
            <link>http://twitter.com/PHPFrame/statuses/12293177832</link>
          </item>
          </channel>

        </rss>
        ';

        $this->_rss->loadXML($str);

        $this->assertEquals("http://twitter.com/PHPFrame", $this->_rss->link());
        $this->assertEquals(5, count($this->_rss->items()));
    }
}
