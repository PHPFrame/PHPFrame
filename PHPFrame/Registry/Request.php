<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	registry
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */
	
/**
 * Request Class
 * 
 * This class encapsulates access to the request arrays and provides input filtering.
 * 
 * The request class is responsible for processing the incoming request according to 
 * the current session's client.
 * 
 * @todo		This class needs to be changed to use PHPFrame_Utils_Filter instead of phpinputfilter
 * @package		PHPFrame
 * @subpackage 	registry
 * @since 		1.0
 */
class PHPFrame_Registry_Request extends PHPFrame_Registry {
	/**
	 * Instance of itself in order to implement the singleton pattern
	 * 
	 * @var object of type PHPFrame_Application_FrontController
	 */
	private static $_instance=null;
	/**
	 * Instance of PHPInputFilter
	 * 
	 * @access	private
	 * @var		object
	 */
	private static $_inputfilter=null;
	/**
	 * A unification array of filtered global arrays
	 * 
	 * @access	private
	 * @var		array
	 */
	private $_array=array();
	private $_response=null;
	
	/**
	 * Constructor
	 * 
	 * @access	protected
	 * @return	void
	 * @since	1.0
	 */
	protected function __construct() {
		if (!isset(self::$_inputfilter)) {
			self::$_inputfilter = new InputFilter();
		}
		
		// Populate request array using session's client
		$this->_array = PHPFrame::getSession()->getClient()->populateURA();
		
		//add other globals
		$this->_array['files'] = $_FILES;
		$this->_array['env'] = $_ENV;
		$this->_array['server'] = $_SERVER;
		
		$this->_response = new PHPFrame_Registry_Response();
	}
	
	/**
	 * Get Instance
	 * 
	 * @static
	 * @access	public
	 * @return 	PHPFrame_Registry
	 * @since	1.0
	 */
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new self;
		}
		
		return self::$_instance;
	}
	
	/**
	 * Get a request variable
	 * 
	 * @access	public
	 * @param	string	$key
	 * @param	mixed	$default_value
	 * @return	mixed
	 * @since	1.0
	 */
	public function get($key, $default_value=null) {
		if (!isset($this->_array['request'][$key]) && !is_null($default_value)) {
			$this->_array['request'][$key] = $default_value;
		}
		
		return $this->_array['request'][$key];
	}
	
	/**
	 * Set a request variable
	 * 
	 * @access	public
	 * @param	string	$key
	 * @param	mixed	$value
	 * @return	void
	 * @since	1.0
	 */
	public function set($key, $value) {
		$this->_array['request'][$key] = self::$_inputfilter->process($value);
	}
	
	/**
	 * Get request/post array from URA
	 * 
	 * @return	array
	 */
	public function getPost() {
		return $this->_array['request'];
	}
	
	/**
	 * Get component name
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getComponentName() {
		// If component has not been set we return the default value
		if (empty($this->_array['request']['component'])) {
			$this->_array['request']['component'] = 'com_dashboard';
		}
		
		return $this->_array['request']['component'];
	}
	
	/**
	 * Set component name
	 * 
	 * @access	public
	 * @param	string	$value The value to set the variable to.
	 * @return	void
	 */
	public function setComponentName($value) {
		// Filter value before assigning to variable
		$this->_array['request']['component'] = self::$_inputfilter->process($value);
	}
	
	/**
	 * Get $_action
	 * 
	 * @static
	 * @access	public
	 * @return	action
	 */
	public function getAction() {
		return $this->_array['request']['action'];
	}
	
	/**
	 * Set $_action.
	 * 
	 * @static
	 * @access	public
	 * @param	string	$value The value to set the variable to.
	 * @return	void
	 */
	public function setAction($value) {
		// Filter value before assigning to variable
		$this->_array['request']['action'] = self::$_inputfilter->process($value);
	}
	
	public function getResponse() {
		return $this->_response;
	}
	
	/**
	 * Destroy the request data
	 * 
	 * @static
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	public function destroy() {
		$this->_array = array();
	}
}
