<?php
/**
 * PHPFrame/Application/Pathway.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * This class is used by objects of type PHPFrame_HTMLDocument. They have an pathway
 * instance used when rendering output in HTML format.
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @see      PHPFrame_HTMLDocument
 * @since    1.0
 */
class PHPFrame_Pathway
{
    /**
     * Associative array containing the pathway items.
     * 
     * @var array
     */
    private $_array=array();
    
    /**
     * Constructor.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        $this->addItem("Home", "index.php");
    }
    
    /**
     * Add a pathway item.
     * 
     * @param string $title The pathway item title.
     * @param string $url   The pathway item URL.
     * 
     * @return void
     * @since  1.0
     */
    public function addItem($title, $url='') 
    {
        $title = (string) $title;
        $url   = (string) $url;
        
        $this->_array[] = array("title"=>$title, "url"=>$url);
    }
    
    /**
     * Convert pathway to array.
     * 
     * @return array
     * @since  1.0
     */
    public function toArray()
    {
        return $this->_array;
    }
}
