<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 * @author 		Luis Montero [e-noise.com]
 */

/**
 * PHPFrame Class
 * 
 * This class provides a factory to create PHPFrame objects.
 * 
 * It also provides information about the installed PHPFrame version.
 * 
 * @package		PHPFrame
 * @since 		1.0
 */
class PHPFrame {
	/**
	 * The PHPFrame version
	 * 
	 * @var string
	 */
	const VERSION='1.0 Alpha';
	
	/**
	 * Get PHPFrame version
	 * 
	 * @return	string
	 * @since 	1.0
	 */
	public static function getVersion() {
		return self::VERSION;
	}
	
	/*
	 * Presentation layer
	 */
	
	/**
	 * Get front controller object
	 * 
	 * @return	object
	 * @since 	1.0
	 */
	public static function getFrontController() {
		return PHPFrame_Application_FrontController::getInstance();
	}
	
	/**
	 * Get component action controller object for given option
	 * 
	 * @param	string	$component_name
	 * @return	object
	 * @since 	1.0
	 */
	public static function getActionController($component_name) {
		$class_name = substr($component_name, 4)."Controller";
		return PHPFrame_Base_Singleton::getInstance($class_name);
	}
	
	/**
	 * Get model
	 * 
	 * @param	$component_name
	 * @param	$model_name
	 * @param	$args
	 * @return	object
	 * @since 	1.0
	 */
	public static function getModel($component_name, $model_name, $args=array()) {
		$class_name = substr($component_name, 4)."Model";
		$class_name .= ucfirst($model_name);
		
		// make a reflection object
		$reflectionObj = new ReflectionClass($class_name);
		
		// Check if class is instantiable
		if ($reflectionObj->isInstantiable()) {
			// Try to get the constructor
			$constructor = $reflectionObj->getConstructor();
			// Check to see if we have a valid constructor method
			if ($constructor instanceof ReflectionMethod) {
				// If constructor is public we create a new instance
				if ($constructor->isPublic()) {
					return $reflectionObj->newInstanceArgs($args);
				}
			}
			// No declared constructor, so we instantiate without args
			return new $class_name;
		}
		elseif ($reflectionObj->hasMethod('getInstance')) {
			$get_instance = $reflectionObj->getMethod('getInstance');
			if ($get_instance->isPublic() && $get_instance->isStatic()) {
				return call_user_func_array(array($class_name, 'getInstance'), $args);
			}
		}
		
		// If we have not been able to return a model object we throw an exception
		throw new Exception($model_name." not supported. Could not get instance of ".$class_name);
	}
	
	/*
	 * Request Registry
	 */
	
	public static function getRequest() {
		return PHPFrame_Registry_Request::getInstance();
	}
	
	/**
	 * Get response object
	 * 
	 * @return object
	 * @since 	1.0
	 */
	public static function getResponse() {
		return self::getRequest()->getResponse();
	}
	
	/*
	 * Session Registry
	 */
	
	/**
	 * Get session object
	 * 
	 * @return	object
	 * @since 	1.0
	 */
	public static function getSession() {
		return PHPFrame_Registry_Session::getInstance();
	}
	
	/**
	 * Get user object
	 * 
	 * @return 	object
	 * @since 	1.0
	 */
	public static function getUser() {
		return self::getSession()->getUser();
	}
	
	/**
	 * Get system events object from session
	 * 
	 * @return	object
	 * @since 	1.0
	 */
	public static function getSysevents() {
		return self::getSession()->getSysevents();
	}
	
	/*
	 * Application Registry
	 */
	
	public static function getApplicationRegistry() {
		return PHPFrame_Registry_Application::getInstance();
	}
	
	/**
	 * Get permissions object
	 * 
	 * @return	object
	 * @since 	1.0
	 */
	public static function getPermissions() {
		return self::getApplicationRegistry()->getPermissions();
	}
	
	/**
	 * Get modules
	 * 
	 * @return	object
	 * @since 	1.0
	 */
	public static function getComponents() {
		return self::getApplicationRegistry()->getComponents();
	}
	
	/**
	 * Get modules
	 * 
	 * @return	object
	 * @since 	1.0
	 */
	public static function getModules() {
		return self::getApplicationRegistry()->getModules();
	}

	

	
	/**
	 * Get a table class.
	 * 
	 * @param	string	$component_name
	 * @param	string	$table_name
	 * @return	object
	 * @since 	1.0
	 */
	public static function getTable($component_name, $table_name) {
		$class_name = substr($component_name, 4)."Table".ucfirst($table_name);
		return PHPFrame_Database_Table::getInstance($class_name);
	}
	
	/**
	 * Get database object
	 * 
	 * @return	object
	 * @since 	1.0
	 */
	public static function getDB() {
		return PHPFrame_Database::getInstance(config::DB_HOST, 
											  config::DB_USER, 
											  config::DB_PASS, 
											  config::DB_NAME);
	}
	
	/**
	 * Get document object
	 * 
	 * @param	string	$type The document type (html or xml)
	 * @return	object
	 * @since 	1.0
	 */
	public static function getDocument($type) {
		return PHPFrame_Base_Singleton::getInstance('PHPFrame_Document_'.strtoupper($type));
	}
	
	/**
	 * Get uri object
	 * 
	 * @return	object
	 * @since 	1.0
	 */
	public static function getURI($uri='') {
		return new PHPFrame_Utils_URI($uri);
	}
	
	/**
	 * Get pathway object
	 * 
	 * @return	object
	 * @since 	1.0
	 */
	public static function getPathway() {
		return PHPFrame_Base_Singleton::getInstance('PHPFrame_Application_Pathway');
	}
}
