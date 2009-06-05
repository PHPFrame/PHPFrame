<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	debug
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Profiler Class
 * 
 * This class still needs to be fleshed out.
 * 
 * @package		PHPFrame
 * @subpackage 	debug
 * @since 		1.0
 */
class PHPFrame_Debug_Profiler {
	private static $_key_count=0;
	/**
	 * An array containing user defined execution milestones
	 * 
	 * @var array
	 */
	private static $_milestones=array();
	
	/**
	 * Constructor
	 * 
	 * The private constructor ensures this class is not instantiated and is alwas used statically.
	 * 
	 * @return void
	 */
	private function __construct() {}
	
	/**
	 * Set milestone in the profiler
	 * 
	 * @param	string	$name
	 * @return	void
	 */
	public static function setMilestone($name) {
		self::$_milestones[self::$_key_count] = array($name, self::_microtime_float());
		self::$_key_count++;
	}
	
	/**
	 * Get Report
	 * 
	 * @return	mixed
	 * @since	1.0
	 */
	public static function getReport($array=false) {
		if ($array) {
			return self::_asArray();
		}
		else {
			return self::_asString();
		}
	}
	
	private static function _asArray() {
		self::setMilestone('End');
		
		foreach (self::$_milestones as $key=>$value) {
			if (isset($prev_key)) {
				$report[$key][0] = $value[0];
				$report[$key][1] = round($value[1] - self::$_milestones[$prev_key][1], 2);
			}
			else {
				$report[$key][0] = self::$_milestones[0][0];
				$report[$key][1] = 0;
			}
			
			$prev_key = $key;
		}
		
		$report['Total'] = round(self::$_milestones[(count(self::$_milestones)-1)][1] - self::$_milestones[0][1], 2);
		
		return $report;
	}
	
	private static function _asString() {
		self::setMilestone('End');
		
		$report = "Profiler\n";
		$report .= "--------\n\n";
		
		foreach (self::$_milestones as $key=>$value) {
			if (isset($prev_key)) {
				$report .= $value[0]." => ";
				$report .= round($value[1] - self::$_milestones[$prev_key][1], 2);
			}
			else {
				$report .= self::$_milestones[0][0]." => 0";
			}
			
			$report .= " msec\n";
			
			$prev_key = $key;
		}
		
		$report .= "Total => ";
		$report .= round(self::$_milestones[(count(self::$_milestones)-1)][1] - self::$_milestones[0][1], 2);
		$report .= " msec";
		
		return $report;
	} 
	
	/**
	 * Calculate current microtime in miliseconds
	 * 
	 * @return	float
	 * @since	1.0
	 */
	private static function _microtime_float() {
	    list ($msec, $sec) = explode(' ', microtime());
	    $microtime = ( (float)$msec + (float)$sec ) * 1000;
	    return $microtime;
	}
}
