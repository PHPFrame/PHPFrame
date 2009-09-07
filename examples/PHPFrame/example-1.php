<?php
require "PHPFrame.php";

$config = PHPFrame::Config();
print_r($config->get("default_lang"));