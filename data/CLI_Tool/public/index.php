<?php
/**
 * data/CLITool/public/index.php
 *
 * PHP version 5
 *
 * The web application index / bootstrap file
 *
 * This is the file that we browse to access the web application.
 *
 * This is reponsible for firing up the application.
 *
 * @category  PHPFrame
 * @package   PHPFrame_CLITool
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

// Include PHPFrame
require_once "PHPFrame.php";

// Get absolute path to application
$install_dir = str_replace(DS.'public', '', dirname(__FILE__));
$user_dir    = PHPFrame_Filesystem::getUserHomeDir().DS.".PHPFrame_CLI_Tool";
PHPFrame_Filesystem::ensureWritableDir($user_dir);

// Create new instance of "Application"
$app = new PHPFrame_Application(array(
    "install_dir" => $install_dir,
    "var_dir" => $user_dir.DS."var",
    "tmp_dir" => $user_dir.DS."tmp"
));

// Handle request
$app->dispatch();
