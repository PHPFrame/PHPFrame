<?php
include_once "PHPFrame.php";

$str_obj = new PHPFrame_String("i am a string & and object...");

echo $str_obj." (".$str_obj->len()." character(s) long)\n";
echo $str_obj->upper()." (Upper case)\n";
echo $str_obj->upperFirst()." (Upper case first character)\n";
echo $str_obj->upperWords()." (Upper case first character of every word)\n";
