<?php
/**
 * PHPFrame/Document/RPCDocument.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * RPC Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @see      PHPFrame_Document
 * @since    1.0
 */
class PHPFrame_RPCDocument extends PHPFrame_XMLDocument
{
    /**
     * Constructor.
     * 
     * @param string $mime    [Optional] The document's MIME type. The default 
     *                        value is 'text/xml'.
     * @param string $charset [Optional] The document's character set. Default 
     *                        value is 'UTF-8'.
     * 
     * @return void
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
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        // Commented out indentation as it was making some of the parsing fail
        // in the clients 
        //return $this->indent($this->dom->saveXML());
        return $this->dom->saveXML();
    }
}