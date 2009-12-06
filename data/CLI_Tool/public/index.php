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
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

// Include PHPFrame
require_once "PHPFrame.php";

// Get absolute path to application
$install_dir = str_replace(DS.'public', '', dirname(__FILE__));

// Create new instance of "Application"
$app = new PHPFrame_Application(array("install_dir"=>$install_dir));

// Handle request
$app->dispatch();
