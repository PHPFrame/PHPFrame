<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	application
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Action Controller class
 * 
 * This class is used to implement the MVC (Model/View/Controller) pattern 
 * in the components.
 * 
 * As an abstract class it has to be extended to be instantiated. This class is 
 * used as a template for creating controllers when developing components. See 
 * the built in components (dashboard, user, admin, ...) for examples.
 * 
 * Controllers process requests and respond to events, typically user actions, 
 * and may invoke changes on data using the available models.
 * 
 * @package		PHPFrame
 * @subpackage 	application
 * @since 		1.0
 * @see 		PHPFrame_Application_Model, PHPFrame_Application_View
 * @abstract 
 */
abstract class PHPFrame_Application_ActionController 
{
	/**
	 * Instances of its concrete children
	 * 
	 * @var array of objects of type PHPFrame_Application_ActionController
	 */
	private static $_instances=array();
	/**
	 * Default controller action
	 * 
	 * @var	string
	 */
	protected $_default_action=null;
	/**
	 * A string containing a url to be redirected to. Leave empty for no redirection.
	 *
	 * @var string
	 */
	protected $_redirect_url=null;
	/**
	 * A reference to the System Events object.
	 * 
	 * This object is used to report system messages from the action controllers.
	 * 
	 * @var	object
	 */
	protected $_sysevents=null;
	/**
	 * This is a flag we use to indicate whether the controller's executed task was successful or not.
	 * 
	 * @var boolean
	 */
	protected $_success=false;
	
	/**
	 * Constructor
	 * 
	 * @return 	void
	 * @since	1.0
	 */
	protected function __construct($default_action) 
	{
		$this->_default_action = (string) $default_action;
		
		// Get reference to System Events object
		$this->_sysevents = PHPFrame::getSysevents();
		
		$components = PHPFrame::getComponents();
		$this->component_info = $components->loadByOption(PHPFrame::getRequest()->getComponentName());
		
		// Add pathway item
		PHPFrame::getPathway()->addItem(ucwords($this->component_info->name), 'index.php?component='.$this->component);
		
		// Append component name in ducument title
		$document = PHPFrame::getDocument('html');
		if (!empty($document->title)) $document->title .= ' - ';
		$document->title .= ucwords($frontcontroller->component_info->name);
	}
	
	/**
	 * Get Instance
	 * 
	 * @param	$class_name	A string with the name of the concrete action controller.
	 * @return PHPFrame_Application_ActionController
	 * @since	1.0
	 */
	public static function getInstance($class_name) 
	{
		if (!isset(self::$_instances[$class_name]) 
			|| !(self::$_instances[$class_name] instanceof PHPFrame_Application_ActionController)) {
			self::$_instances[$class_name] = new $class_name;
		}
		
		return self::$_instances[$class_name];
	}
	
	/**
	 * Execute action
	 * 
	 * This method executes a given task (runs a named member method).
	 *
	 * @return 	void
	 * @since	1.0
	 */
	public function execute() 
	{
		// Get action from the request
		$request_action = PHPFrame::getRequest()->getAction();
		//echo $request_action; exit;
		// If no specific action has been requested we use default action
		if (empty($request_action)) {
			$action = $this->_default_action;
		}
		else {
			$action = $request_action;
		}
		
		// Check permissions before we execute
		$component = PHPFrame::getRequest()->getComponentName();
		$groupid = PHPFrame::getSession()->getGroupId();
		$permissions = PHPFrame::getPermissions();
		if ($permissions->authorise($component, $action, $groupid) === true) {
			if (is_callable(array($this, $action))) {
				// Start buffering
				ob_start();
				$this->$action();
				// save buffer in response object
				$action_output = ob_get_contents();
				// clean output buffer
				ob_end_clean();
			}
			else {
				throw new PHPFrame_Exception("Action ".$action."() not found in controller.");
			}
		}
		else {
			if (!PHPFrame::getSession()->isAuth()) {
				$this->setRedirect('index.php?component=com_login');
			}
			else {
				$this->_sysevents->setSummary('Permission denied.');
			}
		}
		
		// Redirect if set by the controller
		$this->redirect();
		
		// Return action's output as string
		return $action_output;
	}
	
	/**
	 * Get controller's success flag
	 * 
	 * @return	boolean
	 * @since	1.0
	 */
	public function getSuccess() 
	{
		return $this->_success;
	}
	
	/**
	 * Cancel
	 * 
	 * Cancel and set redirect to index.
	 *
	 * @return 	void
	 * @since	1.0
	 */
	protected function cancel() 
	{
		$this->setRedirect( 'index.php' );
	}
	
	/**
	 * Set redirection url
	 * 
	 * Set the redirection URL.
	 *
	 * @param string $url
	 * @return 	void
	 * @since	1.0
	 */
	protected function setRedirect($url) 
	{
		$this->_redirect_url = PHPFrame_Utils_Rewrite::rewriteURL($url, false);
	}
	
	/**
	 * Redirect
	 * 
	 * Redirect browser to redirect URL.
	 * @return 	void
	 * @since	1.0
	 */
	protected function redirect() 
	{
		if ($this->_redirect_url && PHPFrame::getSession()->getClientName() != "cli") {
			header("Location: ".$this->_redirect_url);
			exit;
		}
	}
	
	/**
	 * Get model
	 * 
	 * Gets a named model within the component.
	 *
	 * @param	string	$name The model name. If empty the view name is used as default.
	 * @return	object
	 * @since	1.0
	 */
	protected function getModel($name, $args=array()) 
	{
		return PHPFrame::getModel(PHPFrame::getRequest()->getComponentName(), $name, $args);
	}
	
	/**
	 * Get view
	 * 
	 * Get a named view within the component.
	 *
	 * @param	string	$name
	 * @return	object
	 * @since	1.0
	 */
	protected function getView($name, $layout='') 
	{
		$class_name = strtolower(substr(PHPFrame::getRequest()->getComponentName(), 4));
		$class_name .= "View".ucfirst($name);
		
		try {
			$reflectionObj = new ReflectionClass($class_name);
		}
		catch (Exception $e) {
			throw new PHPFrame_Exception($e->getMessage());
		}
		
		if ($reflectionObj->isSubclassOf( new ReflectionClass("PHPFrame_Application_View") )) {
			return new $class_name($layout);
		}
		else {
			throw new PHPFrame_Exception("View class '".$class_name."' not supported.");
		}
	}
}
