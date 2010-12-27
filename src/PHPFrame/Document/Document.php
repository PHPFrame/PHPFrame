<?php
/**
 * PHPFrame/Document/Document.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Document
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
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
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
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
    private $_charset = "utf-8";
    /**
     * The document title
     *
     * @var string
     */
    private $_title = "";
    /**
     * The document body
     *
     * @var string
     */
    private $_body = "";

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
        $this->mime($mime);

        if (!is_null($charset)) {
            $this->charset($charset);
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
            "mime_type" => $this->mime(),
            "charset"   => $this->charset(),
            "title"     => $this->title(),
            "body"      => $this->body()
        );

        return new ArrayIterator($array);
    }

    /**
     * Get/set the document's character set
     *
     * @param string $str [Optional] The character set to use for the document.
     *
     * @return string
     * @since  1.0
     */
    public function charset($str=null)
    {
        if (!is_null($str)) {
            $this->_charset = (string) $str;
        }

        return $this->_charset;
    }

    /**
     * Get/set document's mime type
     *
     * @param string $str [Optional] The MIME type to use for the document.
     *
     * @return string
     * @since  1.0
     */
    public function mime($str=null)
    {
        if (!is_null($str)) {
            $this->_mime_type = (string) $str;
        }

        return $this->_mime_type;
    }

    /**
     * Get/set the document title
     *
     * @param string $str [Optional] The string to set as document title.
     *
     * @return string
     * @since  1.0
     */
    public function title($str=null)
    {
        if (!is_null($str)) {
            $this->_title = (string) $str;
        }

        return $this->_title;
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
     * Get/set the document body.
     *
     * @param string $str String containing the document body.
     *
     * @return string
     * @since  1.0
     */
    public function body($str=null)
    {
        if (!is_null($str)) {
            $this->_body = (string) $str;
        }

        return (string) $this->_body;
    }

    /**
     * Append string to the document body
     *
     * @param string $str String to append the document body.
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
     * @param string $str String to prepend to the document body.
     *
     * @return void
     * @since  1.0
     */
    public function prependBody($str)
    {
        $this->_body = (string) $str.$this->_body;
    }
}
