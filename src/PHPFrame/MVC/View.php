<?php
/**
 * PHPFrame/MVC/View.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   MVC
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * This class is used to implement the MVC (Model/View/Controller) pattern.
 * 
 * Views are used to render the output of a controller into a form suitable for 
 * interaction, typically a user interface element. Multiple views can exist 
 * for a single controller for different purposes.
 * 
 * @category PHPFrame
 * @package  MVC
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @see      PHPFrame_ActionController
 * @since    1.0
 */
class PHPFrame_View implements IteratorAggregate
{
    /**
     * The view name
     * 
     * @var string
     */
    private $_name = null;
    /**
     * Data array for view
     * 
     * @var array
     */
    private $_data = array();
    /**
     * Response object used to display the view
     * 
     * @var PHPFrame_Document
     */
    private $_response;
    
    /**
     * Constructor
     * 
     * @param string             $name     The view name
     * @param PHPFrame_Document  $document Document object used to display the 
     *                                     view 
     * @param PHPFrame_IRenderer $renderer Renderer object used to render the 
     *                                     view
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(
        $name, 
        PHPFrame_Document $document, 
        PHPFrame_IRenderer $renderer
    ) {
        $this->_name     = trim((string) $name);
        $this->_document = $document;
        $this->_renderer = $renderer;
    }
    
    /**
     * Convert view to string
     * 
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";
        
        foreach ($this->_data as $data) {
            $str .= print_r($data, true)."\n";
        }
        
        return $str;
    }
    
    /**
     * Implementation of IteratorAggregate interface
     * 
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }
    
    /**
     * Get view name
     * 
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
     * @return void
     * @since  1.0
     */
    public function display() 
    {
        $this->_response->getDocument()->setBody($this->_response->getRenderer()->render($this));
    }
}
