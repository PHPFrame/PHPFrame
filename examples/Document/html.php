<?php
require "PHPFrame.php";

$doc = new PHPFrame_HTMLDocument();
$doc->title("I am the document title");
$doc->body("Lorem ipsum ...");

echo $doc;