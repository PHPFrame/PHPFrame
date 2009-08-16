<?php
//Hack to override path to PHPFrame source
$PHPFrame_path = "/Users/lupomontero/Documents/workspace/PHPFrame/src";
set_include_path($PHPFrame_path . PATH_SEPARATOR . get_include_path());
require "PHPFrame.php";

$file_path = "/Users/lupomontero/Desktop";
$file = new PHPFrame_FS_FileInfo($file_path, true);

var_dump($file);
