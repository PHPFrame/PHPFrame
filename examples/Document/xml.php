<?php
require "PHPFrame.php";

$doc = new PHPFrame_XMLDocument();
$doc->title("I am the document title");

$note = $doc->addNode("note", null, array("priority"=>"high"));
$doc->addNode("to", $note, null, "Myself");
$doc->addNode("from", $note, null, "Myself");
$doc->addNode("subject", $note, null, "What are you doing writing unit tests on a Sunday night?");

echo $doc;
