<?php
require "PHPFrame.php";

$file_path = "/Users/lupomontero/Desktop";
$file_info = new PHPFrame_FileInfo($file_path);

print_r(iterator_to_array($file_info));