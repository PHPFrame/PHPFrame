<?php
/**
 * public/index.php
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
 * @package   PHPFrame_AppTemplate
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Installation constants
 */
define('PHPFRAME_INSTALL_DIR', str_replace(DIRECTORY_SEPARATOR."public", "", dirname(__FILE__)));
define("PHPFRAME_CONFIG_DIR", PHPFRAME_INSTALL_DIR.DIRECTORY_SEPARATOR."etc");
define("PHPFRAME_TMP_DIR", PHPFRAME_INSTALL_DIR.DIRECTORY_SEPARATOR."tmp");
define("PHPFRAME_VAR_DIR", PHPFRAME_INSTALL_DIR.DIRECTORY_SEPARATOR."var");

// Include PHPFrame main file
require_once "PHPFrame.php";

// Fire up PHPFrame
PHPFrame::Fire();
