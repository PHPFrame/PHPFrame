<?php
/**
 * PHPFrame/Document/RPCDocument.php
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
 * RPC Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Document
 * @since    1.0
 */
class PHPFrame_RPCDocument extends PHPFrame_XMLDocument
{
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @uses   DOMImplementation, PHPFrame_URI, PHPFrame_Pathway
     * @since  1.0 
     */
    public function __construct($mime="text/xml", $charset=null) 
    {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);
        
        // Add body
        $this->addNode($this->dom, "methodResponse");       
    }
    
    /**
     * Convert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return $this->indent($this->dom->saveXML());
    }
}