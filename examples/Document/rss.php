<?php
require "PHPFrame.php";

$doc = new PHPFrame_RSSDocument();
$doc->title("I am the document title");
$doc->link("http://groups.google.com/group/phpframe-dev/feed/rss_v2_0_msgs.xml");
$doc->description("Some really cool feed...");
$doc->addItem("An article", "http://127.0.0.1", "blah blah blah");
$doc->addItem("Another article", "http://127.0.0.1", "blah blah blah");

echo $doc;
