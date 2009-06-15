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
 * @version    SVN: $Id: Document.php 71 2009-06-14 11:54:03Z luis.montero@e-noise.com $
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Document Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Document
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Document
{
    /**
     * Document title
     *
     * @var         string
     * @access  public
     */
    var $title = '';

    /**
     * Document description
     *
     * @var         string
     * @access  public
     */
    var $description = '';

    /**
     * Document URI
     *
     * @var         string
     * @access  public
     */
    var $uri = '';

    /**
     * Document base URL
     *
     * @var         string
     * @access  public
     */
    var $base = '';

     /**
     * Contains the document language setting
     *
     * @var         string
     * @access  public
     */
    var $language = 'en-gb';

    /**
     * Document modified date
     *
     * @var        string
     * @access    private
     */
    var $_mdate = '';

    /**
     * Tab string
     *
     * @var        string
     * @access    private
     */
    var $_tab = "\11";

    /**
     * Contains the line end string
     *
     * @var        string
     * @access    private
     */
    var $_lineEnd = "\12";

    /**
     * Contains the character encoding string
     *
     * @var         string
     * @access  private
     */
    var $_charset = 'utf-8';

    /**
     * Document mime type
     *
     * @var        string
     * @access    private
     */
    var $_mime = 'text/html';

    /**
     * Document namespace
     *
     * @var        string
     * @access    private
     */
    var $_namespace = '';
    
    /**
     * Constructor
     * 
     * @return    void
     * @access    public
     * @since    1.0
     */
    public function __construct() 
    {
        $uri = new PHPFrame_Utils_URI();
        $this->base = $uri->getBase();
        $this->uri = $uri->__toString();
    }
}
