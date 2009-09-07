<?php
include_once "PHPFrame.php";

$str_obj = new PHPFrame_String("i am a string");

var_dump(
    $str_obj, 
    $str_obj->len(), 
    $str_obj->html(), 
    $str_obj->upper(), 
    $str_obj->upperFirst(), 
    $str_obj->upperWords()
);
