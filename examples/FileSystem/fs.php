<?php
require "PHPFrame.php";

$file_path = "/Users/lupomontero/Desktop";
$file_info = new PHPFrame_FileInfo($file_path, true);

var_dump($file_info);
