#!/usr/bin/env php
<?php
// Require PEAR_Config class if it hasnt been declared yet
if (!class_exists("PEAR_Config")) {
    require "PEAR/Config.php";
}

// Build path to CLI tool
$path_to_cli_tool = PEAR_Config::singleton()->get("php_dir");
$path_to_cli_tool .= DIRECTORY_SEPARATOR;
$path_to_cli_tool .= "PHPFrame_CLI_Tool".DIRECTORY_SEPARATOR;
$path_to_cli_tool .= "public".DIRECTORY_SEPARATOR;
$path_to_cli_tool .= "index.php";

// Load CLI Tool app
require $path_to_cli_tool;
