<?php
/**
 * PHPFrame/MVC/View.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   MVC
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * This class is used to implement the MVC (Model/View/Controller) pattern.
 * 
 * Views are used to render the output of a controller into a form suitable for 
 * interaction, typically a user interface element. Multiple views can exist for 
 * a single controller for different purposes.
 * 
 * @category PHPFrame
 * @package  MVC
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_ActionController
 * @since    1.0
 */
class PHPFrame_View
{
    /**
     * The view name
     * 
     * @var string
     */
    protected $_name = null;
    /**
     * Data array for view
     * 
     * @var array
     */
    protected $_data = array();
    
    /**
     * Constructor
     * 
     * @param string $name   The view name
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($name) 
    {
        $this->_name = trim((string) $name);
    }
    
    /**
     * Get view name
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Add a variable to data array
     * 
     * @param string $key   The name of the variable inside the view.
     * @param mixed  $value The variable we want to add to the view.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addData($key, $value) 
    {
        $this->_data[$key] = $value;
    }
    
    /**
     * Get view data
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * Display the view
     * 
     * This method loads the template layer of the view.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function display() 
    {
        // Set profiler milestone
        PHPFrame_Profiler::instance()->addMilestone();
        
        $document = PHPFrame::Response()->getDocument()->renderView($this);
    }
}
