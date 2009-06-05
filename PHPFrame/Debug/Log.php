<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	debug
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Log Class
 * 
 * @package		PHPFrame
 * @subpackage 	debug
 * @since 		1.0
 */
class PHPFrame_Debug_Log {
	/**
	 * Write string to log file
	 * 
	 * @static
	 * @access	public
	 * @param	string	$str	The string to append to log file
	 * @return	void
	 */
	public static function write($str) {
		// Add log info
		$info = "\n";
		$info .= "---";
		$info .= "\n";
		$info .= "[".date("Y-m-d H:i:s")."]";
		$info .= " [ip:".$_SERVER['REMOTE_ADDR']."]";
		$info .= " [client: ".PHPFrame::getSession()->getClientName()."]";
		$info .= "\n";
		
		// Write log to filesystem using PHPFrame's utility class
		PHPFrame_Utils_Filesystem::write(config::LOG_FILE, $info.$str, true);
	}
}
