<?php
require "PHPFrame.php";

echo "PHPFrame_PlainDocument\n\n";

$doc = new PHPFrame_PlainDocument();
$doc->setTitle("I am the document title");
$doc->setBody("Lorem ipsum ...");

//print_r(iterator_to_array($doc));
echo $doc;

echo "\n\nPHPFrame_XMLDocument\n\n";

$doc = new PHPFrame_XMLDocument();
$doc->setTitle("I am the document title");
$doc->setBody("Lorem ipsum ...");

//print_r(iterator_to_array($doc));
echo $doc;

echo "\n\nPHPFrame_HTMLDocument\n\n";

$doc = new PHPFrame_HTMLDocument();
$doc->setTitle("I am the document title");
$doc->setBody("Lorem ipsum ...");

//print_r(iterator_to_array($doc));
echo $doc;

echo "\n\nPHPFrame_RPCDocument\n\n";

$doc = new PHPFrame_RPCDocument();
$doc->setTitle("I am the document title");
$doc->setBody("Lorem ipsum ...");

//print_r(iterator_to_array($doc));
echo $doc;
