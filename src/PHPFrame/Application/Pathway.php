<?php
/**
 * PHPFrame/Application/Pathway.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Pathway Class
 * 
 * This class is used by objects of type PHPFrame_Document_HTML. They have an pathway
 * instance used when rendering output in HTML format.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Application
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Document_HTML
 * @since      1.0
 */
class PHPFrame_Application_Pathway
{
    /**
     * Associative array containing the pathway items
     * 
     * @var array
     */
    private $_array=array();
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct() 
    {
        $this->addItem("Home", "index.php");
    }
    
    /**
     * Add a pathway item
     * 
     * @param string $title The pathway item title.
     * @param string $url   The pathway item URL.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addItem($title, $url='') 
    {
        $title = (string) $title;
        $url   = (string) $url;
        
        $this->pathway[] = array("title"=>$title, "url"=>$url);
    }
    
    /**
     * Echo pathway as HTML
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function display() 
    {
        echo '<div class="pathway">';
        for ($i=0; $i<count($this->_array); $i++) {
            if ($i>0) {
                echo ' &gt;&gt; ';
            }
            echo '<span class="pathway_item">';
            if (!empty($this->_array[$i]['url'])) {
                echo '<a href="'.$this->_array[$i]['url'].'">'.$this->_array[$i]['title'].'</a>';
            }
            else {
                echo $this->_array[$i]['title'];
            }
            echo '</span>';
        }
        echo '</div>';
    }
}
