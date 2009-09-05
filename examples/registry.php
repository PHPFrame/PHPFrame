<?php
include_once "PHPFrame.php";
/**
 * Installation constants
 */
define('PHPFRAME_INSTALL_DIR', dirname(__FILE__));
define("PHPFRAME_CONFIG_DIR", PHPFRAME_INSTALL_DIR.DIRECTORY_SEPARATOR."etc");
define("PHPFRAME_TMP_DIR", PHPFRAME_INSTALL_DIR.DIRECTORY_SEPARATOR."tmp");
define("PHPFRAME_VAR_DIR", PHPFRAME_INSTALL_DIR.DIRECTORY_SEPARATOR."var");


PHPFrame::Env();

var_dump(
    PHPFrame::AppRegistry(),
    PHPFrame::Session(),
    PHPFrame::Request()
);
