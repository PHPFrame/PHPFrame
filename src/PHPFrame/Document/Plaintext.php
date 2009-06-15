<?php
/**
 * PHPFrame/Document/Plaintext.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Document
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * XML Document Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Document
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Document_Plaintext extends PHPFrame_Document
{
    private $_text="";
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0 
     */
    public function __construct() 
    {
        // Call parent's constructor to set mime type
        parent::__construct('text/plain');
    }
    
    /**
     * Render view and store in document's body
     * 
     * This method is invoked by the views and renders the ouput data in the
     * document specific format.
     * 
     * @param PHPFrame_MVC_View $view The view object to process.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function renderView(PHPFrame_MVC_View $view)
    {
        foreach ($view->getData() as $key=>$value) {
            if ($value instanceof PHPFrame_Database_RowCollection) {
                $value = $this->renderRowCollection($value);
            }
            $this->_text .= $key.": \n".$value."\n";
        }
    }
    
    /**
     * Method used to render Row Collections in this document
     * 
     * @param PHPFrame_Database_RowCollection
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function renderRowCollection(PHPFrame_Database_RowCollection $collection)
    {
        $str = "(RowCollection) \n";
        
        $str .= (string) $collection;
        
        return $str;
    }
    
    /**
     * Covert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function toString()
    {
        $str = $this->_title."\n\n";
        $str .= $this->_text;
        
        return $str;
    }
}
