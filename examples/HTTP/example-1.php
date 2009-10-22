<?php
require "PHPFrame.php";

$url = "http://www.phpframe.org";
$http_request = new PHPFrame_HTTPRequest($url);
print_r($http_request->send());
