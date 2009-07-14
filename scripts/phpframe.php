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
//echo $abs_path; exit;
define('_ABS_PATH', dirname(__FILE__));

// Include PHPFrame main file
include_once PEAR_INSTALL_DIR.DIRECTORY_SEPARATOR.'PEAR.php';
include_once PEAR_INSTALL_DIR.DIRECTORY_SEPARATOR.'PHPFrame.php';

if (!class_exists('PHPFrame')) {
    die("Missing PHPFrame. Please check your PEAR installation.\n");
}

PEAR::setErrorHandling(PEAR_ERROR_DIE);

// Include autoloader
//require_once _ABS_PATH.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."autoload.php";

// Fire up PHPFrame
PHPFrame::Fire();
