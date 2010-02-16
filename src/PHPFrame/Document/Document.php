<?php
/**
 * PHPFrame/Document/Document.php
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
 * This is an abstract class that all "Document" objects extend.
 * 
 * PHPFrame provides 4 implementations of this abstract class:
 * 
 * - Plaintext
 * - XML
 * - HTML (specialised XML document for HTML responses).
 * - RPC (specialised XML document used for XML-RPC responses.)
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 * @abstract
 */
abstract class PHPFrame_Document implements IteratorAggregate
{
    /**
     * Document mime type
     *
     * @var string
     */
    private $_mime_type = null;
    /**
     * Contains the character encoding string
     *
     * @var string
     */
    private $_charset = 'UTF-8';
    /**
     * The document title
     * 
     * @var string
     */
    private $_title = null;
    /**
     * The document body
     * 
     * @var string
     */
    private $_body = null;
    
    /**
     * Constructor
     * 
     * @param string $mime    The document's MIME type.
     * @param string $charset [Optional] The document's character set. Default 
     *                        value is 'UTF-8'.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($mime, $charset=null) 
    {
        $this->_mime_type = (string) $mime;
        
        if (!is_null($charset)) {
            $this->_charset = (string) $charset;
        }
    }
    
    /**
     * Magic method used when object is used as string
     * 
     * @return string
     * @since  1.0
     */
    abstract public function __toString();
    
    /**
     * Implementation of the IteratorAggregate interface.
     * 
     * @return Iterator
     * @since  1.0
     */
    public function getIterator()
    {
        $array = array(
            "mime_type" => $this->getMimeType(),
            "charset"   => $this->getCharset(),
            "title"     => $this->getTitle(),
            "body"      => $this->getBody()
        );
        
        return new ArrayIterator($array);
    }
    
    /**
     * Get the document's character set
     * 
     * @return string
     * @since  1.0
     */
    public function getCharset()
    {
        return $this->_charset;
    }
    
    /**
     * Get document's mime type
     * 
     * @return string
     * @since  1.0
     */
    public function getMimeType()
    {
        return $this->_mime_type;
    }
    
    /**
     * Get the document title
     * 
     * @return string
     * @since  1.0
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
    /**
     * Set the document title
     * 
     * @param string $str The string to set as document title.
     * 
     * @return void
     * @since  1.0
     */
    public function setTitle($str)
    {
        $this->_title = (string) $str;
    }
    
    /**
     * Append string to the document title
     * 
     * @param string $str The string to append.
     * 
     * @return void
     * @since  1.0
     */
    public function appendTitle($str)
    {
        $this->_title .= $str;
    }
    
    /**
     * Get the document body
     * 
     * @return string
     * @since  1.0
     */
    public function getBody()
    {
        return $this->_body;
    }
    
    /**
     * Set the document body
     * 
     * @param string $str String containing the document body.
     * 
     * @return void
     * @since  1.0
     */
    public function setBody($str)
    {
        $this->_body = (string) $str;
    }
    
    /**
     * Append string to the document body
     * 
     * @param string $str String containing the document body.
     * 
     * @return void
     * @since  1.0
     */
    public function appendBody($str)
    {
        $this->_body .= (string) $str;
    }
    
    /**
     * Prepend string to the document body
     * 
     * @param string $str String containing the document body.
     * 
     * @return void
     * @since  1.0
     */
    public function prependBody($str)
    {
        $this->_body = (string) $str.$this->getBody();
    }
}
