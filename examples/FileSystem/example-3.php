<?php
require "PHPFrame.php";

// Create file object in write mode
$file_path = "/Users/lupomontero/Desktop/anotherfile.txt";
$file_obj  = new PHPFrame_FileObject($file_path, "w");

// Write some content into the file
$file_obj->fwrite("Some content...");

// Close file
unset($file_obj);

// Reopen file in read mode
$file_obj = new PHPFrame_FileObject($file_path);

// Loop through every line until end of file and echo each line 
while (!$file_obj->eof()) {
    echo $file_obj->fgets();
}