<?php
require "PHPFrame.php";

//$url = "http://dist.phpframe.org/features/PHPFrame_Features_Users-0.0.1.tgz";
//$http_request = new PHPFrame_HTTPRequest($url);
//print_r($http_request->download("/Users/lupomontero/Desktop"));

$url = "http://www.phpframe.org";
$http_request = new PHPFrame_HTTPRequest($url);
print_r($http_request->send());
