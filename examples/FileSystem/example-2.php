<?php
require "PHPFrame.php";

$file_path = "/Users/lupomontero/Desktop/somefile.txt";
$file_info = new PHPFrame_FileInfo($file_path);

foreach ($file_info->openFile() as $line) {
    echo $line;
}