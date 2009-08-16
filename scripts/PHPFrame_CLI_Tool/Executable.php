#!/usr/bin/env php
<?php
$path_to_cli_tool = PEAR_Config::singleton()->get("php_dir");
$path_to_cli_tool = DIRECTORY_SEPARATOR;
$path_to_cli_tool .= "PHPFrame_CLI_Tool".DIRECTORY_SEPARATOR;
$path_to_cli_tool .= "public".DIRECTORY_SEPARATOR;
$path_to_cli_tool .= "index.php";

require $path_to_cli_tool;