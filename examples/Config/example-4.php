<?php
require "PHPFrame.php";

foreach (PHPFrame::Config() as $key=>$value) {
    echo $key.': '.$value."\n";
}