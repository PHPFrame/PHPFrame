<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	registry
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Response Class
 * 
 * @package		PHPFrame
 * @subpackage 	registry
 * @since 		1.0
 */
class PHPFrame_Registry_Response {
	private $_header=null;
	private $_body=null;
	
	function setHeader($str) {
		$this->_header = $str;
	}
	
	function setBody($str) {
		$this->_body = $str;
	}
	
	function send() {
		echo $this->_body;
		
		if (config::DEBUG) {
			echo '<pre>'.PHPFrame_Debug_Profiler::getReport().'</pre>';
		}
	}
}
