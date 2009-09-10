<?php
/**
 * PHPFrame/Document/PlainDocument.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * XML Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_PlainDocument extends PHPFrame_Document
{
    /**
     * The qualified name of the document type to create. 
     * 
     * @var string
     */
    protected $qualified_name = "plain";
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0 
     */
    public function __construct($mime="text/plain", $charset=null) 
    {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);
    }
    
    /**
     * Covert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str = "";
        
        if ($this->title) {
            $str .= $this->title."\n\n";
        }
        
        $str .= $this->body;
        
        return $str;
    }
    
    public function render(PHPFrame_View $view)
    {
        parent::render($view);
        
        $sysevents   = PHPFrame::Session()->getSysevents();
        $this->body  = (string) $sysevents."\n\n".$this->body;
    }
    
    /**
     * Method used to render Row Collections in this document
     * 
     * @param PHPFrame_DatabaseRowCollection
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function renderRowCollection(PHPFrame_DatabaseRowCollection $collection)
    {
        $str  = "(RowCollection) \n";
        $str .= (string) $collection;
        
        return $str;
    }
}
