<?php
/**
 * PHPFrame/Document/Document.php
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
 * Document Class
 * 
 * This is an abstract class that all "Document" objects extend.
 * 
 * PHPFrame provides 3 implementations of this abstract class:
 * 
 * - HTML
 * - XML
 * - Plaintext
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Document
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 * @abstract
 */
abstract class PHPFrame_Document
{
    /**
     * The document title
     * 
     * @var string
     */
    protected $title=null;
    /**
     * Contains the character encoding string
     *
     * @var string
     */
    protected $charset='utf-8';
    /**
     * Document mime type
     *
     * @var string
     */
    protected $mime_type=null;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($mime, $charset=null) 
    {
        $this->mime_type = (string) $mime;
        
        if (!is_null($charset)) {
            $this->charset = (string) $charset;
        }
    }
    
    /**
     * Magic method used when object is used as string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Set the document title
     * 
     * @param string $str The string to set as document title.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setTitle($str)
    {
        $this->title = (string) $str;
    }
    
    /**
     * Append string to the document title
     * 
     * @param string $str The string to append.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function appendTitle($str)
    {
        $this->title .= $str;
    }
    
    /**
     * Get the documeny title
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Get the document's character set
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getCharset()
    {
        return $this->charset;
    }
    
    /**
     * Get document's mime type
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }
    
    public function render($object)
    {
        if ($a) {
            
        }   
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
    abstract public function renderView(PHPFrame_MVC_View $view);
    
    /**
     * Method used to render Row Collections in this document
     * 
     * @param PHPFrame_Database_RowCollection
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    abstract public function renderRowCollection(PHPFrame_Database_RowCollection $collection);
    
    /**
     * Covert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    abstract public function toString();
}
