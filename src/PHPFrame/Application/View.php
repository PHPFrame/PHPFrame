<?php
/**
 * @version       SVN: $Id$
 * @package       PHPFrame
 * @subpackage    application
 * @copyright     2009 E-noise.com Limited
 * @license       http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * View Class
 * 
 * This class is used to implement the MVC (Model/View/Controller) architecture 
 * in the components.
 * 
 * Views are used to render the output of a component into a form suitable for 
 * interaction, typically a user interface element. Multiple views can exist for 
 * a single component for different purposes.
 * 
 * This class should be extended when creating component views as it is an 
 * abstract class. This class is used as a template for creating views when 
 * developing components. See the built in components (dashboard, user, admin, ...) 
 * for examples.
 * 
 * @abstract
 * @package        PHPFrame
 * @subpackage     application
 * @see            PHPFrame_Application_ActionController, PHPFrame_Application_Model 
 * @since         1.0
 */
abstract class PHPFrame_Application_View 
{
    /**
     * The view name
     * 
     * @var string
     */
    protected $_name=null;
    /**
     * The layout to load. Typical values: "list", "detail", "form", ...
     * 
     * @var string
     */
    protected $_layout=null;
    /**
     * Data array for view
     * 
     * @var    array
     */
    protected $_data=array();
    
    /**
     * Constructor
     * 
     * @since    1.0
     * @return    void
     */
    public function __construct($name, $layout) 
    {
        $this->_name = (string) $name;
        $this->_layout = (string) $layout;
    }
    
    /**
     * Add a variable to data array
     * 
     * @param    string    $key
     * @param    string    $value
     * @return    void
     */
    public function addData($key, $value) 
    {
        $this->_data[$key] = $value;
    }
    
    /**
     * Display the view
     * 
     * This method loads the template layer of the view.
     * 
     * This method  also trigger layout specific methods. 
     * For example, if we are displaying layout "list" and there is a method called 
     * displayMyviewList within the extended view class this method will be automatically invoked.
     *
     * @since    1.0
     */
    public function display() 
    {
        // If there is a layout specific method we trigger it before rendering
        $layout_array = explode('_', $this->_layout);
        $layout = '';
        for ($i=0; $i<count($layout_array); $i++) {
            $layout .= ucfirst($layout_array[$i]);
        }
        $tmpl_specific_method = "display".ucfirst($this->_name).ucfirst($layout);
        if (method_exists($this, $tmpl_specific_method)) {
            // Invoke layout specific display method
            $this->$tmpl_specific_method();
        }
        
        // Render view depending on client
        $this->_render();
    }
    
    /**
     * This method renders the view using the request's client
     *
     * @since    1.0
     */
    protected function _render() 
    {
        // Add view name and template name to data array
        $this->_data['view'] = $this->_name;
        $this->_data['layout'] = $this->_layout;
        
        // Delegate rendering to request's client object
        $client = PHPFrame::getSession()->getClient();
        $client->renderView($this->_data);
    }
}
