<?php
/**
 * PHPFrame/MVC/View.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage MVC
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
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
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage MVC
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_MVC_ActionController, PHPFrame_MVC_Model
 * @since      1.0
 * @abstract
 */
abstract class PHPFrame_MVC_View
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
     * @var array
     */
    protected $_data=array();
    
    /**
     * Constructor
     * 
     * @param string $name   The view name
     * @param string $layout Optional parameter to specify a specific layout.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($name, $layout) 
    {
        $this->_name = (string) $name;
        $this->_layout = (string) $layout;
    }
    
    /**
     * Add a variable to data array
     * 
     * @param string $key   The name of the variable inside the view.
     * @param mixed  $value The variable we want to add to the view.
     * 
     * @return void
     * @since  1.0
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
     * For example, if we are displaying layout "list" and there is a method 
     * called displayMyviewList within the extended view class this method 
     * will be automatically invoked.
     * 
     * @return void
     * @since  1.0
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
     * @access protected
     * @return void
     * @since  1.0
     */
    protected function _render() 
    {
        // Add view name and template name to data array
        $this->_data['view'] = $this->_name;
        $this->_data['layout'] = $this->_layout;
        
        // Delegate rendering to request's client object
        $client = PHPFrame::Session()->getClient();
        $client->renderView($this->_data);
    }
}
