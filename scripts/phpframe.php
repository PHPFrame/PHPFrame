#!/usr/bin/env php
<?php
/**
 * scripts/phpframe.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage PHPFrame_CLI
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Set constant containing absolute path to application
 */
$abs_path = PEAR_Config::singleton()->get('install_dir');
$abs_path .= DIRECTORY_SEPARATOR."PHPFrame";
echo $abs_path; exit;

define('_ABS_PATH', $abs_path);

// Include PHPFrame main file
require_once "PHPFrame.php";

// Include autoloader
//require_once _ABS_PATH.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."autoload.php";

// Fire up PHPFrame
PHPFrame::Fire();
